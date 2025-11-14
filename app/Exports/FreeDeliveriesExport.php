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
use Illuminate\Support\Facades\Schema;

class FreeDeliveriesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'orderStatus',
            'user',
            'creator',
            'oldest.site',
            'latest.site',
            'product',
            'wheel',
            'purchase',
            'order_details',
            'transportation_amount.order_amountable.route',
        ])->where('address_id', '>', 0);
        
        // Handle search - broadened to more relations/fields (case-insensitive)
        if ($this->request->filled('search')) {
            $searchTerm = trim($this->request->input('search'));
            $searchLower = strtolower($searchTerm);
            $pattern = "%{$searchLower}%";
            $query->where(function($q) use ($searchTerm, $searchLower, $pattern) {
                // numeric id search
                if (is_numeric($searchTerm)) {
                    $q->whereRaw('CAST(id AS CHAR) LIKE ?', [$pattern]);
                } else {
                    // cast id to text for non-numeric partial matches (Postgres-safe)
                    $q->whereRaw("CAST(id AS CHAR) LIKE ?", [$pattern]);
                }

                // only add order_number clause if column exists to avoid SQL errors on PG
                if (Schema::hasColumn('orders', 'order_number')) {
                    $q->orWhereRaw('LOWER(order_number) LIKE ?', [$pattern]);
                }

                $q->orWhereHas('user', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('product', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('creator', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('oldest.site', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  });
            });
        }

        // Date filters (created_at) - supports start, end, or both
        if ($this->request->filled('start_date') || $this->request->filled('end_date')) {
            try {
                if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
                    $start = Carbon::parse($this->request->input('start_date'))->startOfDay();
                    $end = Carbon::parse($this->request->input('end_date'))->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                } elseif ($this->request->filled('start_date')) {
                    $start = Carbon::parse($this->request->input('start_date'))->startOfDay();
                    $query->where('created_at', '>=', $start);
                } else {
                    $end = Carbon::parse($this->request->input('end_date'))->endOfDay();
                    $query->where('created_at', '<=', $end);
                }
            } catch (\Exception $e) {
                // Invalid date input: ignore date filter (fail-safe)
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
        // Get PO/PI
        $poPi = $order->user ? $order->user->name : 'N/A';

        // Get Customer
        $customer = optional($order->oldest->site)->name ?? 'N/A';

        // Get Quarry/Site (city)
        $quarrySite = $order->oldest->site->city ?? 'N/A';

        // Get Product
        $product = optional($order->product)->name ?? 'N/A';

        // Get Agent
        $agent = optional($order->creator)->name ?? 'N/A';

        // Get Unit
        $unit = $order->unit ?? 'N/A';

        // Get Price per unit
        $pricePerUnit = 'N/A';
        if (isset($order->price_per_unit)) {
            $pricePerUnit = number_format($order->price_per_unit / 100, 2);
        } elseif (isset($order->cost_amount)) {
            $pricePerUnit = number_format($order->cost_amount / 100, 2);
        }

        // Get Tonne
        $tonne = $order->oldest->quantity ?? ($order->order_details->sum('quantity') ?? 'N/A');

        // Calculate Fee
        $feeAmount = 0;
        $transport = $order->transportation_amount;
        if (!is_null($transport) && isset($transport->amount) && !is_null($order->oldest) && isset($order->oldest->total_kg) && $order->oldest->total_kg > 0) {
            $feeAmount = $transport->amount / ($order->oldest->total_kg / 1000);
        }
        $fee = number_format($feeAmount, 2);

        // Get Distance
        $distance = 'N/A';
        if ($transport && optional($transport->order_amountable)->route && optional($transport->order_amountable->route)->distance_text) {
            $distance = $transport->order_amountable->route->distance_text;
        }

        // Get Duration
        $duration = 'N/A';
        if ($transport && optional($transport->order_amountable)->route) {
            $duration = optional($transport->order_amountable->route)->traffic_text ?? 
                       optional($transport->order_amountable->route)->duration_text ?? 
                       'N/A';
        }

        // Get Wheel
        $wheel = optional($order->wheel)->wheel ?? 'N/A';

        // Get Start At
        $startAt = optional($order->purchase)->created_at ? $order->purchase->created_at->format('M d, Y H:i:s') : 'N/A';

        // Get Quantity
        $quantity = $order->latest->total ?? ($order->order_details->sum('quantity') ?? 'N/A');

        // Get Completed and Ongoing
        $completed = $order->completed ?? 0;
        $ongoing = $order->ongoing ?? 0;

        // Determine Status
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

        // Get Created At
        $createdAt = $order->created_at ? $order->created_at->format('d M Y H:i') : 'N/A';

        return [
            $order->id,
            $poPi,
            $customer,
            $quarrySite,
            $product,
            $agent,
            $unit,
            $pricePerUnit,
            $tonne,
            $fee,
            $distance,
            $duration,
            $wheel,
            $startAt,
            $quantity,
            $completed,
            $ongoing,
            $status,
            $createdAt,
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
                    'startColor' => ['rgb' => '22C55E']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
