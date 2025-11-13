<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobStatus;
use App\Models\JobDetail;
use App\Exports\JobsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class JobController extends Controller
{
    public function jobs(Request $request)
    {
        // Build query with necessary relationships for the table
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
        if ($request->filled('search')) {
            $searchTerm = strtolower(addcslashes($request->search, '%_'));
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
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Handle status filter (virtual status, not DB column) using subqueries for performance
        if ($request->filled('status')) {
            $status = $request->status;
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
                // Jobs with no assigned, ongoing, or completed trips
                $query->whereDoesntHave('trips', function($q) {
                    $q->whereIn('status', ['assigned', 'ongoing', 'completed']);
                });
            }
        }

        // Sort by created date to show newest jobs first
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $perPage = (int) $request->get('per_page', 10);
        $jobs = $query->paginate($perPage)->appends($request->query());

        // Cache job statuses since they don't change often
        $jobStatuses = cache()->remember('job_statuses', now()->addHour(), function() {
            return JobStatus::all();
        });

        return view('jobs/jobsList', compact('jobs', 'jobStatuses'));
    }

    public function exportJobs(Request $request)
    {
        // Generate filename with current date and filters
        $filename = 'jobs_' . now()->format('Y-m-d_His');
        
        if ($request->has('status') && !empty($request->status)) {
            $filename .= '_' . str_replace(' ', '_', strtolower($request->status));
        }
        
        $filename .= '.xlsx';
        
        return Excel::download(new JobsExport($request), $filename);
    }

    public function jobDetails($id)
    {
        // Use eager loading but without specifying columns to avoid schema compatibility issues
        $job = Job::with([
                'driver',
                'customer',
                'jobDetails'
            ])
            ->jobEvent()  // Add jobEvent scope to get trip-related attributes
            ->findOrFail((int) $id); // Cast to integer for security and performance
        
        return view('jobs/jobDetails', compact('job'));
    }

    public function jobStatuses(Request $request)
    {
        // Start a simple query
        $query = JobStatus::query();

        // Handle search (case-insensitive)

        if ($request->filled('search')) {
            $searchTerm = strtolower(addcslashes($request->search, '%_'));
            $pattern = '%' . $searchTerm . '%';
            $query->whereRaw('LOWER(status) LIKE ?', [$pattern]);
        }

        // Consider caching the results if they don't change often
        $cacheKey = 'job_statuses_' . md5($request->fullUrl());
        $perPage = (int) $request->get('per_page', 10);
        if (config('app.debug') === false && $request->missing('search')) {
            $statuses = cache()->remember($cacheKey, now()->addHour(), function() use ($query, $perPage) {
                return $query->orderBy('status', 'asc')->paginate($perPage);
            });
        } else {
            $statuses = $query->orderBy('status', 'asc')->paginate($perPage);
        }

        // Efficient status counts using DB queries
        $statusCounts = [
            'Accepted' => Job::doesntHave('trips')->count(),
            'Assigned' => Job::whereHas('trips', function($q) { $q->where('status', 'assigned'); })->count(),
            'Ongoing' => Job::whereHas('trips', function($q) { $q->where('status', 'ongoing'); })->count(),
            'Completed' => Job::whereHas('trips', function($q) { $q->where('status', 'completed'); })->count(),
        ];

        return view('jobs/jobStatuses', compact('statuses', 'statusCounts'));
    }
}