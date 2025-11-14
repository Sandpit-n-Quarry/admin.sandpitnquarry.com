<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripStatus;
use App\Exports\TripsExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class TripController extends Controller
{
    public function trips(Request $request)
    {
        // Option 1: Optimize using proper eager loading with all needed relationships
        $query = Trip::with([
            'creator',
            'job.order.customer',
            'job.order.product',
            'job.order.wheel',
            'job.order.transportation_amount',
            'job.order.oldest.site',
            'job.order.latest.site',
            'latest.assignment.driver.user',
            'latest.assignment.truck.transporter'
        ]);
        
        // Handle search (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $pattern = "%{$searchTerm}%";
            $query->where(function($q) use ($pattern, $searchTerm) {
                $q->whereRaw('CAST(id AS TEXT) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(CAST(code AS TEXT)) LIKE ?', [$pattern]);
                
                // Order ID search - cast to text first, then apply LOWER
                $q->orWhereHas('job', function($subQ) use ($searchTerm, $pattern) {
                    $subQ->whereRaw('LOWER(CAST(order_id AS TEXT)) = ?', [$searchTerm])
                         ->orWhereRaw('LOWER(CAST(order_id AS TEXT)) LIKE ?', [$pattern]);
                });
                
                // User: job.order.customer.name
                $q->orWhereHas('job.order.customer', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                });

                // Transporter: job.transporter (name, registration_number)
                $q->orWhereHas('job.transporter', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                         ->orWhereRaw('LOWER(registration_number) LIKE ?', [$pattern]);
                });

                // Transporter via latest.assignment.truck.transporter
                $q->orWhereHas('latest.assignment.truck.transporter', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                         ->orWhereRaw('LOWER(registration_number) LIKE ?', [$pattern]);
                });

                // Driver name via latest assignment
                $q->orWhereHas('latest.assignment.driver.user', function($subQ) use ($pattern) {
                    $subQ->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                });

                // Truck plate/registration via latest.assignment.truck or truck.latest
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
        if ($request->has('status') && $request->status !== 'All Status') {
            $query->where('status', $request->status);
        }
        
        // Handle created_at date range filter
        if ($request->has('created_start_date') && !empty($request->created_start_date)) {
            try {
                $start = Carbon::parse($request->created_start_date)->startOfDay();
                $query->where('created_at', '>=', $start);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        if ($request->has('created_end_date') && !empty($request->created_end_date)) {
            try {
                $end = Carbon::parse($request->created_end_date)->endOfDay();
                $query->where('created_at', '<=', $end);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }
        
        // Handle updated_at date range filter
        if ($request->has('updated_start_date') && !empty($request->updated_start_date)) {
            try {
                $start = Carbon::parse($request->updated_start_date)->startOfDay();
                $query->where('updated_at', '>=', $start);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        if ($request->has('updated_end_date') && !empty($request->updated_end_date)) {
            try {
                $end = Carbon::parse($request->updated_end_date)->endOfDay();
                $query->where('updated_at', '<=', $end);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        // Paginate results
        // If per_page is explicitly set to null or empty string, use default value
        $perPage = $request->input('per_page');
        if ($perPage === null || $perPage === '') {
            $perPage = 10;
        }
        // Cast to integer to ensure proper pagination
        $perPage = (int) $perPage;
        $trips = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Get all trip statuses for filter dropdown
        $tripStatuses = TripStatus::all();
        
        return view('trips/tripsList', compact('trips', 'tripStatuses'));
    }

    public function exportTrips(Request $request)
    {
        // Generate filename with current date and filters
        $filename = 'trips_' . now()->format('Y-m-d_His');
        
        if ($request->has('status') && $request->status !== 'All Status') {
            $filename .= '_' . str_replace(' ', '_', strtolower($request->status));
        }
        
        $filename .= '.xlsx';
        
        return Excel::download(new TripsExport($request), $filename);
    }

    public function tripDetails($id)
    {
        $trip = Trip::with(['tripStatus', 'driver', 'truck', 'trip_details', 'tripLocation'])
                   ->findOrFail($id);
        
        return view('trips/tripDetails', compact('trip'));
    }

    public function tripStatuses(Request $request)
    {
        $query = TripStatus::withCount('trips');
        
        // Handle search (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $pattern = "%{$searchTerm}%";
            $query->whereRaw('LOWER(status) LIKE ?', [$pattern]);
        }

        // Paginate results
        $perPage = $request->get('per_page', 10);
    // Order by the status column (there is no `name` column)
    $statuses = $query->orderBy('status', 'asc')->paginate($perPage);
        
        return view('trips/tripStatuses', compact('statuses'));
    }
}