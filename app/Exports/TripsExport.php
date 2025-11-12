<?php

namespace App\Exports;

use App\Models\Trip;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TripsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Build the query based on filters
     */
    public function query()
    {
        $query = Trip::with([
            'creator',
            'job.order.customer',
            'job.order.product',
            'job.order.wheel',
            'job.order.transportation_amount.order_amountable.route',
            'job.order.oldest.site',
            'job.order.latest.site',
            'latest.assignment.driver.user',
            'latest.assignment.truck.transporter'
        ]);

        // Handle search (case-insensitive)
        if ($this->request->has('search') && !empty($this->request->search)) {
            $searchTerm = strtolower($this->request->search);
            $pattern = "%{$searchTerm}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('CAST(id AS TEXT) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(CAST(code AS TEXT)) LIKE ?', [$pattern]);
                
                $q->orWhereHas('job.order.customer', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                });

                $q->orWhereHas('job.transporter', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                         ->orWhereRaw('LOWER(registration_number) LIKE ?', [$pattern]);
                });

                $q->orWhereHas('latest.assignment.truck.transporter', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                         ->orWhereRaw('LOWER(registration_number) LIKE ?', [$pattern]);
                });

                $q->orWhereHas('latest.assignment.driver.user', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                });

                $q->orWhereHas('latest.assignment.truck', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(registration_plate_number) LIKE ?', [$pattern]);
                });

                $q->orWhereHas('job.order.oldest.site', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                });

                $q->orWhereHas('job.order.latest.site', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                });
            });
        }

        // Handle status filter
        if ($this->request->has('status') && $this->request->status !== 'All Status') {
            $query->where('status', $this->request->status);
        }
        
        // Handle created_at date range filter
        if ($this->request->has('created_start_date') && !empty($this->request->created_start_date)) {
            try {
                $start = Carbon::parse($this->request->created_start_date)->startOfDay();
                $query->where('created_at', '>=', $start);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        if ($this->request->has('created_end_date') && !empty($this->request->created_end_date)) {
            try {
                $end = Carbon::parse($this->request->created_end_date)->endOfDay();
                $query->where('created_at', '<=', $end);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }
        
        // Handle updated_at date range filter
        if ($this->request->has('updated_start_date') && !empty($this->request->updated_start_date)) {
            try {
                $start = Carbon::parse($this->request->updated_start_date)->startOfDay();
                $query->where('updated_at', '>=', $start);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        if ($this->request->has('updated_end_date') && !empty($this->request->updated_end_date)) {
            try {
                $end = Carbon::parse($this->request->updated_end_date)->endOfDay();
                $query->where('updated_at', '<=', $end);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'ID',
            'Order ID',
            'PO/PI',
            'User',
            'Transporter',
            'Driver',
            'Truck',
            'Quarry',
            'Site',
            'Product',
            'Unit',
            'Wheel',
            'Agent',
            'Price / Unit (MYR)',
            'Fee (MYR)',
            'Distance',
            'Duration',
            'Created At',
            'Status',
            'Updated At',
        ];
    }

    /**
     * Map each trip to an array for export
     */
    public function map($trip): array
    {
        // Calculate fee
        $transport = $trip->job && $trip->job->order ? $trip->job->order->transportation_amount : null;
        $feeAmount = 0;
        
        if (!is_null($transport) && isset($transport->amount)) {
            $oldest = $trip->job->order->oldest ?? null;
            if ($oldest && isset($oldest->total_kg) && $oldest->total_kg > 0) {
                $tonnes = $oldest->total_kg / 1000;
                $feeAmount = $transport->amount / $tonnes;
            } else {
                $feeAmount = $transport->amount;
            }
            $feeAmount = $feeAmount / 100;
        }

        // Get distance
        $distance = 'N/A';
        if ($transport && 
            optional($transport->order_amountable)->route &&
            optional($transport->order_amountable->route)->distance_text) {
            $distance = $transport->order_amountable->route->distance_text;
        } elseif ($trip->distance_km) {
            $distance = number_format($trip->distance_km, 1) . ' km';
        }

        // Get duration
        $duration = 'N/A';
        if ($transport && optional($transport->order_amountable)->route) {
            $duration = optional($transport->order_amountable->route)->traffic_text ?? 
                       optional($transport->order_amountable->route)->duration_text ?? 
                       'N/A';
        } elseif ($trip->duration_minutes) {
            $duration = $trip->duration_minutes . ' mins';
        }

        // Get quarry
        $quarry = 'N/A';
        if ($trip->job && $trip->job->order && $trip->job->order->oldest && $trip->job->order->oldest->site) {
            $quarry = $trip->job->order->oldest->site->name ?? 'N/A';
        } elseif ($trip->job && $trip->job->order && $trip->job->order->latest && $trip->job->order->latest->site) {
            $quarry = $trip->job->order->latest->site->name ?? 'N/A';
        }

        // Get site
        $site = 'N/A';
        if ($trip->job && $trip->job->order && $trip->job->order->oldest && $trip->job->order->oldest->site) {
            $site = $trip->job->order->oldest->site->city ?? 'N/A';
        } elseif ($trip->job && $trip->job->order && $trip->job->order->latest && $trip->job->order->latest->site) {
            $site = $trip->job->order->latest->site->city ?? 'N/A';
        }

        return [
            $trip->id,
            $trip->job ? $trip->job->order_id : 'N/A',
            $trip->job && $trip->job->order && $trip->job->order->customer ? $trip->job->order->customer->name : 'N/A',
            $trip->job && $trip->job->order && $trip->job->order->customer ? $trip->job->order->customer->name : 'N/A',
            ($trip->latest && $trip->latest->assignment && $trip->latest->assignment->truck) ? ($trip->latest->assignment->truck->transporter->name ?? 'N/A') : 'N/A',
            ($trip->latest && $trip->latest->assignment && $trip->latest->assignment->driver) ? ($trip->latest->assignment->driver->user->name ?? 'N/A') : 'N/A',
            ($trip->latest && $trip->latest->assignment && $trip->latest->assignment->truck) ? ($trip->latest->assignment->truck->registration_plate_number ?? 'N/A') : 'N/A',
            $quarry,
            $site,
            $trip->job && $trip->job->order && $trip->job->order->product ? $trip->job->order->product->name : 'N/A',
            $trip->job && $trip->job->order ? $trip->job->order->unit : 'N/A',
            $trip->job && $trip->job->order && $trip->job->order->wheel ? $trip->job->order->wheel->wheel : 'N/A',
            $trip->creator ? $trip->creator->name : 'N/A',
            ($trip->job && $trip->job->order && $trip->job->order->price_per_unit) ? number_format($trip->job->order->price_per_unit/100, 2) : 'N/A',
            number_format($feeAmount, 2),
            $distance,
            $duration,
            $trip->created_at ? $trip->created_at->format('M d, Y H:i:s') : 'N/A',
            $trip->status ?? 'Unknown',
            $trip->updated_at ? $trip->updated_at->format('M d, Y H:i:s') : 'N/A',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4A90E2']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
