<?php

namespace App\Exports;

use App\Models\Job;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class JobsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Job::with([
            'jobDetails',
            'latest',
            'order.customer',
            'order.product',
            'order.wheel',
            'order.oldest.site',
            'order.latest.site',
            'order.address.latest',
            'creator',
            'trips',
            'trips.latest.assignment.driver.user'
        ])->jobEvent();

        // Handle search (case-insensitive)
        if ($this->request->has('search') && !empty($this->request->search)) {
            $searchTerm = strtolower(addcslashes($this->request->search, '%_'));
            $pattern = "%{$searchTerm}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('CAST(id AS CHAR) LIKE ?', [$pattern])
                  ->orWhereRaw('CAST(order_id AS CHAR) LIKE ?', [$pattern])
                  ->orWhereHas('order.product', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('order.customer', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                })
                  ->orWhereHas('order.oldest.site', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  });
            });
        }

        // Handle date filters
        if ($this->request->has('start_date') && !empty($this->request->start_date)) {
            try {
                $query->whereDate('created_at', '>=', $this->request->start_date);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }
        if ($this->request->has('end_date') && !empty($this->request->end_date)) {
            try {
                $query->whereDate('created_at', '<=', $this->request->end_date);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        // Handle status filter
        if ($this->request->has('status') && !empty($this->request->status)) {
            $status = $this->request->status;
            $query->whereHas('trips', function($q) use ($status) {
                if ($status === 'Completed') {
                    $q->where('status', 'completed');
                } elseif ($status === 'Ongoing') {
                    $q->where('status', 'ongoing');
                } elseif ($status === 'Assigned') {
                    $q->where('status', 'assigned');
                }
            });
            if ($status === 'Accepted') {
                $query->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['assigned', 'ongoing', 'completed']);
                });
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
            'Customer',
            'Quarry',
            'Site',
            'Product',
            'Unit',
            'Wheel',
            'Price / Unit (MYR)',
            'Agent',
            'Quantity',
            'Assigned',
            'Ongoing',
            'Completed',
            'Status',
            'Created At',
        ];
    }

    /**
     * Map each job to an array for export
     */
    public function map($job): array
    {
        // Get quarry
        $quarry = 'N/A';
        if ($job->order && $job->order->oldest && $job->order->oldest->site) {
            $quarry = $job->order->oldest->site->name;
        } elseif ($job->order && $job->order->latest && $job->order->latest->site) {
            $quarry = $job->order->latest->site->name;
        }

        // Get site/destination
        $site = 'N/A';
        if ($job->order && $job->order->oldest && $job->order->oldest->site) {
            $site = $job->order->oldest->site->city ?? 'N/A';
        } elseif ($job->order && $job->order->latest && $job->order->latest->site) {
            $site = $job->order->latest->site->city ?? 'N/A';
        }

        // Calculate trip counts
        $assigned = 0;
        $ongoing = 0;
        $completed = 0;
        
        if ($job->trips) {
            $assigned = $job->trips->where('status', 'Assigned')->count();
            $ongoing = $job->trips->whereIn('status', ['Started', 'Loading', 'Loaded', 'Unloading', 'Arriving', 'Arrived', 'Notified', 'Waiting', 'Confirmed'])->count();
            $completed = $job->trips->where('status', 'Completed')->count();
        }

        // Determine status
        $status = 'Accepted';
        if ($completed > 0 && $completed >= ($job->order->oldest->quantity ?? 0)) {
            $status = 'Completed';
        } elseif ($ongoing > 0) {
            $status = 'Ongoing';
        } elseif ($assigned > 0) {
            $status = 'Assigned';
        }

        return [
            $job->id,
            $job->order_id ?? 'N/A',
            $job->order && $job->order->customer ? $job->order->customer->name : 'N/A',
            $job->order && $job->order->customer ? $job->order->customer->name : 'N/A',
            $quarry,
            $site,
            $job->order && $job->order->product ? $job->order->product->name : 'N/A',
            $job->order ? $job->order->unit : 'N/A',
            $job->order && $job->order->wheel ? $job->order->wheel->wheel : 'N/A',
            $job->order && $job->order->price_per_unit ? number_format($job->order->price_per_unit / 100, 2) : 'N/A',
            $job->creator ? $job->creator->name : 'N/A',
            $job->order && $job->order->oldest ? $job->order->oldest->quantity : 'N/A',
            $assigned,
            $ongoing,
            $completed,
            $status,
            $job->created_at ? $job->created_at->format('M d, Y H:i:s') : 'N/A',
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
