<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Order::with([
            'purchase.business_price_purchase',
            'user',
            'oldest.site',
            'latest.site',
            'address.latest',
            'product',
            'creator',
            'transportation_amount',
            'wheel',
            'latest',
            'oldest',
            'orderStatus',
            'order_details',
            'jobs.trips'
        ]);

        // Apply search filter if provided (case-insensitive)
        if ($this->request->has('search') && !empty($this->request->search)) {
            $search = $this->request->search;
            $searchLower = strtolower($search);
            $pattern = "%{$searchLower}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('CAST(id AS CHAR) LIKE ?', [$pattern])
                  ->orWhereHas('user', function($q) use ($pattern) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('product', function($q) use ($pattern) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('creator', function($q) use ($pattern) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  });
            });
        }

        // Apply status filter if provided
        if ($this->request->has('status') && $this->request->status !== 'All Status') {
            $status = $this->request->status;
            $query->whereHas('orderStatus', function($q) use ($status) {
                $q->where('name', $status);
            });
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
            'PO/PI',
            'Customer',
            'Quarry / Site',
            'Product',
            'Agent',
            'Unit',
            'Price / Unit (MYR)',
            'Tonne',
            'Fee (MYR)',
            'Distance',
            'Duration',
            'Wheel',
            'Start At',
            'Quantity',
            'Completed',
            'Ongoing',
            'Status',
            'Created At',
        ];
    }

    /**
     * Map each order to an array for export
     */
    public function map($order): array
    {
        // Calculate completed and ongoing counts from jobs and trips
        $completed = 0;
        $ongoing = 0;
        
        if ($order->jobs) {
            foreach ($order->jobs as $job) {
                if ($job->trips) {
                    $completed += $job->trips->where('status', 'Completed')->count();
                    $ongoing += $job->trips->whereIn('status', ['Assigned', 'Started', 'Loading', 'Loaded', 'Unloading', 'Arriving', 'Arrived', 'Notified', 'Waiting', 'Confirmed'])->count();
                }
            }
        }

        // Get quarry/site
        $quarrySite = 'N/A';
        if ($order->oldest && $order->oldest->site) {
            $quarrySite = $order->oldest->site->name;
        } elseif ($order->latest && $order->latest->site) {
            $quarrySite = $order->latest->site->name;
        }

        // Get transportation amount
        $transportAmount = 'N/A';
        if ($order->transportation_amount && $order->transportation_amount->amount) {
            $transportAmount = number_format($order->transportation_amount->amount / 100, 2);
        }

        // Get distance
        $distance = 'N/A';
        if ($order->transportation_amount && 
            optional($order->transportation_amount->order_amountable)->route &&
            optional($order->transportation_amount->order_amountable->route)->distance_text) {
            $distance = $order->transportation_amount->order_amountable->route->distance_text;
        }

        // Get duration
        $duration = 'N/A';
        if ($order->transportation_amount && 
            optional($order->transportation_amount->order_amountable)->route) {
            $duration = optional($order->transportation_amount->order_amountable->route)->traffic_text ?? 
                       optional($order->transportation_amount->order_amountable->route)->duration_text ?? 
                       'N/A';
        }

        // Determine order status
        $status = 'Unknown';
        if ($order->orderStatus) {
            $status = $order->orderStatus->name;
        } elseif ($completed >= ($order->oldest->quantity ?? 0)) {
            $status = 'Completed';
        } elseif (isset($order->latest->status) && $order->latest->status == 'Cancelled') {
            $status = 'Cancelled';
        } else {
            $status = 'Incomplete';
        }

        return [
            $order->id,
            $order->user ? $order->user->name : 'N/A',
            $order->user ? $order->user->name : 'N/A',
            $quarrySite,
            $order->product ? $order->product->name : 'N/A',
            $order->creator ? $order->creator->name : 'N/A',
            $order->unit ?? 'N/A',
            $order->price_per_unit ? number_format($order->price_per_unit / 100, 2) : 'N/A',
            $order->oldest && $order->oldest->total_kg ? number_format($order->oldest->total_kg / 1000, 2) : 'N/A',
            $transportAmount,
            $distance,
            $duration,
            $order->wheel ? $order->wheel->wheel : 'N/A',
            $order->oldest && $order->oldest->start_at ? Carbon::parse($order->oldest->start_at)->format('M d, Y H:i') : 'N/A',
            $order->oldest ? $order->oldest->quantity : 'N/A',
            $completed,
            $ongoing,
            $status,
            $order->created_at ? $order->created_at->format('M d, Y H:i:s') : 'N/A',
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
