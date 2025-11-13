<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Wheel;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Site;
use App\Models\User;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function orders(Request $request)
    {
        // Parse per_page parameter and ensure it's an integer
        $perPage = (int)$request->input('per_page', 10);
        
        // Get all order statuses for the filter dropdown
        $orderStatuses = OrderStatus::all();

        // Start building the query
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
        if ($request->filled('search')) {
            $search = $request->input('search');
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
        if ($request->filled('status') && $request->input('status') !== 'All Status') {
            $status = $request->input('status');
            $query->whereHas('orderStatus', function($q) use ($status) {
                $q->where('name', $status);
            });
        }

        // Get paginated results
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Calculate additional properties needed for the view
        $orders->each(function ($order) {
            // Set defaults for properties
            $order->completed = $order->completed ?? 0;
            $order->ongoing = $order->ongoing ?? 0;
            
            // Calculate completed and ongoing counts from jobs and trips
            if ($order->jobs) {
                $order->jobs->each(function ($job) use (&$order) {
                    if ($job->trips) {
                        $completedTrips = $job->trips->where('status', 'Completed')->count();
                        $ongoingTrips = $job->trips->whereIn('status', ['Assigned', 'Started', 'Loading', 'Loaded', 'Unloading', 'Arriving', 'Arrived', 'Notified', 'Waiting', 'Confirmed'])->count();
                        
                        $order->completed += $completedTrips;
                        $order->ongoing += $ongoingTrips;
                    }
                });
            }
            
            // Format monetary values
            if ($order->price_per_unit) {
                $order->price_per_unit = number_format($order->price_per_unit / 100, 2);
            }
            
            if ($order->transportation_amount && $order->transportation_amount->amount) {
                $order->transportation_amount->amount = number_format($order->transportation_amount->amount / 100, 2);
            }
            
            // Determine order status for display
            if (!$order->orderStatus) {
                if ($order->completed >= ($order->oldest->quantity ?? 0)) {
                    $order->orderStatus = (object)['name' => 'Completed'];
                } else if (isset($order->latest->status) && $order->latest->status == 'Cancelled') {
                    $order->orderStatus = (object)['name' => 'Cancelled'];
                } else {
                    $order->orderStatus = (object)['name' => 'Incomplete'];
                }
            }
        });
        
        return view('orders/ordersList', compact('orders', 'orderStatuses'));
    }

    public function orderDetails($id)
    {
        $order = Order::with([
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
            'jobs.trips.trip_details.assignment.driver.user',
            'jobs.trips.trip_details.assignment.truck',
            'orderPayment'
        ])->findOrFail($id);
        
        // Calculate additional properties
        $order->completed = 0;
        $order->ongoing = 0;
        
        if ($order->jobs) {
            foreach ($order->jobs as $job) {
                if ($job->trips) {
                    $order->completed += $job->trips->where('status', 'Completed')->count();
                    $order->ongoing += $job->trips->whereIn('status', ['Assigned', 'Started', 'Loading', 'Loaded', 'Unloading', 'Arriving', 'Arrived', 'Notified', 'Waiting', 'Confirmed'])->count();
                }
            }
        }
        
        // Format monetary values
        if ($order->price_per_unit) {
            $order->price_per_unit = number_format($order->price_per_unit / 100, 2);
        }
        
        if ($order->transportation_amount && $order->transportation_amount->amount) {
            $order->transportation_amount->amount = number_format($order->transportation_amount->amount / 100, 2);
        }
        
        return view('orders/orderDetails', compact('order'));
    }

    public function exportOrders(Request $request)
    {
        // Generate filename with current date and filters
        $filename = 'orders_' . now()->format('Y-m-d_His');
        
        if ($request->has('status') && $request->status !== 'All Status') {
            $filename .= '_' . str_replace(' ', '_', strtolower($request->status));
        }
        
        $filename .= '.xlsx';
        
        return Excel::download(new OrdersExport($request), $filename);
    }

    public function orderStatuses(Request $request)
    {
    $query = OrderStatus::withCount('orders');
        
        // Handle search (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $pattern = '%' . strtolower($searchTerm) . '%';
            $query->whereRaw('LOWER(status) LIKE ?', [$pattern]);
        }

    // Paginate results
        $perPage = $request->get('per_page', 10);
    // OrderStatus table uses `status` column (no `name` column)
    $statuses = $query->orderBy('status', 'asc')->paginate($perPage);
        
        return view('orders/orderStatuses', compact('statuses'));
    }

public function freeDeliveries(Request $request)
    {
        // Also, only include orders with address_id > 0
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
        if ($request->filled('search')) {
            $searchTerm = trim($request->input('search'));
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
        if ($request->filled('start_date') || $request->filled('end_date')) {
            try {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = Carbon::parse($request->input('start_date'))->startOfDay();
                    $end = Carbon::parse($request->input('end_date'))->endOfDay();
                    $query->whereBetween('created_at', [$start, $end]);
                } elseif ($request->filled('start_date')) {
                    $start = Carbon::parse($request->input('start_date'))->startOfDay();
                    $query->where('created_at', '>=', $start);
                } else {
                    $end = Carbon::parse($request->input('end_date'))->endOfDay();
                    $query->where('created_at', '<=', $end);
                }
            } catch (\Exception $e) {
                // Invalid date input: ignore date filter (fail-safe)
            }
        }

        // Paginate results and keep query string so pagination preserves filters
        $perPage = (int)$request->get('per_page', 10);
        $freeDeliveries = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Process each order for display
        $freeDeliveries->each(function ($order) {
            // Set defaults for properties
            $order->completed = $order->completed ?? 0;
            $order->ongoing = $order->ongoing ?? 0;
            
            // Keep transportation_amount as numeric MYR value (float), not a formatted string
            if ($order->transportation_amount && isset($order->transportation_amount->amount)) {
                $amt = $order->transportation_amount->amount;
                // numeric (cents) -> convert to unit (e.g. MYR)
                if (is_numeric($amt)) {
                    $order->transportation_amount->amount = $amt / 100;
                } else {
                    // try clean string then convert
                    $clean = str_replace(',', '', $amt);
                    if (is_numeric($clean)) {
                        $order->transportation_amount->amount = $clean / 100;
                    }
                }
            }
        });
        
        return view('orders/freeDeliveries', compact('freeDeliveries'));
    }
    /**
     * Display a listing of self-pickup orders
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function selfPickups(Request $request)
    {
        // Query orders where address_id <= 0 (self-pickup)
        $query = Order::with([
                    'orderStatus',
                    'customer',
                    'product',
                    'wheel', 
                    'quarry',
                    'agent',
                    'jobs',
                    'trips'
                ])
                ->where(function($q) {
                    $q->where('address_id', '<=', 0);
                });
        
        // Handle search (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $searchLower = strtolower($searchTerm);
            $pattern = "%{$searchLower}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('LOWER(order_number) LIKE ?', [$pattern])
                  ->orWhereHas('customer', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                          ->orWhereRaw('LOWER(email) LIKE ?', [$pattern])
                          ->orWhereRaw('LOWER(phone) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('product', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  })
                  ->orWhereHas('quarry', function($subQ) use ($pattern) {
                      $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                  });
            });
        }

        // Paginate results
        $perPage = $request->get('per_page', 10);
        $selfPickups = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Calculate completed and ongoing quantities
        foreach ($selfPickups as $order) {
            // Count completed trips/jobs
            $order->completed_quantity = $order->trips->where('status', 'Completed')->sum('actual_quantity');
            
            // Count ongoing trips/jobs
            $order->ongoing_quantity = $order->trips->whereIn('status', ['Pending', 'In Progress'])->sum('actual_quantity');
        }
        
        return view('orders.selfPickups', compact('selfPickups'));
    }
    
    /**
     * Show the form for editing the specified order.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function orderEdit($id)
    {
        $order = Order::with([
            'orderStatus',
            'customer',
            'product',
            'wheel',
            'quarry',
            'agent',
            'jobs',
            'trips'
        ])->findOrFail($id);
        
        // Calculate completed and ongoing quantities
        $order->completed_quantity = $order->trips->where('status', 'Completed')->sum('actual_quantity');
        $order->ongoing_quantity = $order->trips->whereIn('status', ['Pending', 'In Progress'])->sum('actual_quantity');
        
        // Get additional data needed for the edit form
        $products = Product::all();
        $wheels = Wheel::all();
        $orderStatuses = OrderStatus::all();
        $customers = Customer::all();
        $sites = Site::all(); // Quarries
        $agents = User::where('role', 'agent')->get();
        
        return view('orders.orderEdit', compact('order', 'products', 'wheels', 'orderStatuses', 'customers', 'sites', 'agents'));
    }
}