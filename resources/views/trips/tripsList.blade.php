@extends('layout.layout')
@php
    $title='Trips List';
    $subTitle = 'Trips Management';
    $script ='<script>
                        $(".remove-item-btn").on("click", function() {
                            $(this).closest("tr").addClass("d-none")
                        });
                        
                        // Select/Deselect all checkboxes
                        $("#selectAll").on("click", function() {
                            var isChecked = $(this).prop("checked");
                            $("input[name=\'checkbox\']").prop("checked", isChecked);
                        });
                        
                        // Date filter handling
                        var today = new Date();
                        var dd = String(today.getDate()).padStart(2, "0");
                        var mm = String(today.getMonth() + 1).padStart(2, "0");
                        var yyyy = today.getFullYear();
                        
                        // Clear date filters
                        $("#clearDateFilters").on("click", function() {
                            $("input[name=\'created_start_date\']").val("");
                            $("input[name=\'created_end_date\']").val("");
                            $("input[name=\'updated_start_date\']").val("");
                            $("input[name=\'updated_end_date\']").val("");
                            $("#date-filter-form").submit();
                        });
            </script>';
@endphp

@section('content')

            <div class="card h-100 p-0 radius-12">
                <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <span class="text-md fw-medium text-secondary-light mb-0">Show</span>
                        <form method="GET" id="per-page-form">
                            <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="per_page" onchange="document.getElementById('per-page-form').submit()">
                                <option value="5" {{ request('per_page') === '5' ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('per_page') === '10' || request('per_page') === null || request('per_page') === '' ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') === '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') === '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') === '100' ? 'selected' : '' }}>100</option>
                            </select>
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                        </form>
                        <form class="navbar-search" method="GET" id="search-form">
                            <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search trips..." value="{{ request('search') }}">
                            <iconify-icon icon="ion:search-outline" class="icon" onclick="document.getElementById('search-form').submit()"></iconify-icon>
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('per_page'))
                                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                            @endif
                            @if(request('created_start_date'))
                                <input type="hidden" name="created_start_date" value="{{ request('created_start_date') }}">
                            @endif
                            @if(request('created_end_date'))
                                <input type="hidden" name="created_end_date" value="{{ request('created_end_date') }}">
                            @endif
                            @if(request('updated_start_date'))
                                <input type="hidden" name="updated_start_date" value="{{ request('updated_start_date') }}">
                            @endif
                            @if(request('updated_end_date'))
                                <input type="hidden" name="updated_end_date" value="{{ request('updated_end_date') }}">
                            @endif
                            <button type="submit" class="d-none"></button>
                        </form>
                        <form method="GET" id="status-form">
                            <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="status" onchange="document.getElementById('status-form').submit()">
                                <option value="All Status" {{ request('status') == 'All Status' ? 'selected' : '' }}>All Status</option>
                                @foreach($tripStatuses as $status)
                                    <option value="{{ $status->status }}" {{ request('status') == $status->status ? 'selected' : '' }}>{{ $status->status }}</option>
                                @endforeach
                            </select>
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request('per_page'))
                                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                            @endif
                            @if(request('created_start_date'))
                                <input type="hidden" name="created_start_date" value="{{ request('created_start_date') }}">
                            @endif
                            @if(request('created_end_date'))
                                <input type="hidden" name="created_end_date" value="{{ request('created_end_date') }}">
                            @endif
                            @if(request('updated_start_date'))
                                <input type="hidden" name="updated_start_date" value="{{ request('updated_start_date') }}">
                            @endif
                            @if(request('updated_end_date'))
                                <input type="hidden" name="updated_end_date" value="{{ request('updated_end_date') }}">
                            @endif
                        </form>
                        
                        <!-- Date filters -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm" type="button" id="dateFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <iconify-icon icon="uil:calendar-alt" class="icon"></iconify-icon>
                                <span class="d-none d-md-inline">Date</span>
                                @if(request('created_start_date') || request('created_end_date') || request('updated_start_date') || request('updated_end_date'))
                                    <span class="badge bg-primary text-white ms-1">!</span>
                                @endif
                            </button>
                            <div class="dropdown-menu p-3" style="width: 450px;" aria-labelledby="dateFilterDropdown">
                                <form method="GET" id="date-filter-form">
                                    <div class="row g-2 mb-2">
                                        <div class="col-12">
                                            <label class="form-label small mb-1">Created Date</label>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-base py-0">From</span>
                                                <input type="date" class="form-control form-control-sm bg-base" name="created_start_date" value="{{ request('created_start_date') }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-base py-0">To</span>
                                                <input type="date" class="form-control form-control-sm bg-base" name="created_end_date" value="{{ request('created_end_date') }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-12">
                                            <label class="form-label small mb-1">Updated Date</label>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-base py-0">From</span>
                                                <input type="date" class="form-control form-control-sm bg-base" name="updated_start_date" value="{{ request('updated_start_date') }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-base py-0">To</span>
                                                <input type="date" class="form-control form-control-sm bg-base" name="updated_end_date" value="{{ request('updated_end_date') }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="button" id="clearDateFilters" class="btn btn-outline-secondary btn-sm">Clear</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                                    </div>
                                    
                                    @if(request('search'))
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request('status'))
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                    @endif
                                    @if(request('per_page'))
                                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('tripStatuses') }}" class="btn btn-outline-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                            <iconify-icon icon="mdi:format-list-bulleted" class="icon text-xl line-height-1"></iconify-icon>
                            Trip Statuses
                        </a>
                    </div>
                </div>
                <div class="card-body p-24">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table sm-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <div class="d-flex align-items-center gap-10">
                                            <div class="form-check style-check d-flex align-items-center">
                                                <input class="form-check-input radius-4 border input-form-dark" type="checkbox" name="checkbox" id="selectAll">
                                            </div>
                                            ID
                                        </div>
                                    </th>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">PO/PI</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Transporter</th>
                                    <th scope="col">Driver</th>
                                    <th scope="col">Truck</th>
                                    <th scope="col">Quarry</th>
                                    <th scope="col">Site</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Wheel</th>
                                    <th scope="col">Agent</th>
                                    <th scope="col">Price / Unit</th>
                                    <th scope="col">Fee</th>
                                    <th scope="col">Distance</th>
                                    <th scope="col">Duration</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Updated At</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trips as $index => $trip)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-10">
                                            <div class="form-check style-check d-flex align-items-center">
                                                <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $trip->id }}">
                                            </div>
                                            {{ $trip->id }}
                                        </div>
                                    </td>
                                    <td>{{ $trip->job ? $trip->job->order_id : 'N/A' }}</td>
                                    <td>{{ $trip->job && $trip->job->order && $trip->job->order->customer ? $trip->job->order->customer->name : 'N/A' }}</td>
                                    <td>{{ $trip->job && $trip->job->order && $trip->job->order->customer ? $trip->job->order->customer->name : 'N/A' }}</td>
                                    <td>
                                        @if($trip->latest && $trip->latest->assignment && $trip->latest->assignment->truck)
                                            {{ $trip->latest->assignment->truck->transporter->name ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($trip->latest && $trip->latest->assignment && $trip->latest->assignment->driver)
                                            {{ $trip->latest->assignment->driver->user->name ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($trip->latest && $trip->latest->assignment && $trip->latest->assignment->truck)
                                            {{ $trip->latest->assignment->truck->registration_plate_number ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($trip->job && $trip->job->order && $trip->job->order->oldest && $trip->job->order->oldest->site)
                                            {{ $trip->job->order->oldest->site->name ?? 'N/A' }}
                                        @elseif($trip->job && $trip->job->order && $trip->job->order->latest && $trip->job->order->latest->site)
                                            {{ $trip->job->order->latest->site->name ?? 'N/A' }}
                                        @else
                                            No Site
                                        @endif
                                    </td>
                                    <td>
                                        @if($trip->job && $trip->job->order && $trip->job->order->oldest && $trip->job->order->oldest->site)
                                            {{ $trip->job->order->oldest->site->city ?? 'N/A' }}
                                        @elseif($trip->job && $trip->job->order && $trip->job->order->latest && $trip->job->order->latest->site)
                                            {{ $trip->job->order->latest->site->city ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $trip->job && $trip->job->order && $trip->job->order->product ? $trip->job->order->product->name : 'N/A' }}</td>
                                    <td>{{ $trip->job && $trip->job->order ? $trip->job->order->unit : 'N/A' }}</td>
                                    <td>{{ $trip->job && $trip->job->order && $trip->job->order->wheel ? $trip->job->order->wheel->wheel : 'N/A' }}</td>
                                    <td>{{ $trip->creator ? $trip->creator->name : 'N/A' }}</td>
                                    <td>
                                        @if($trip->job && $trip->job->order && $trip->job->order->price_per_unit)
                                            MYR {{ number_format($trip->job->order->price_per_unit/100, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @php 
                                            $transport = $trip->job && $trip->job->order ? $trip->job->order->transportation_amount : null;
                                            $feeAmount = 0;
                                            
                                            // Check if transport amount exists
                                            if (!is_null($transport) && isset($transport->amount)) {
                                                // Check if we can calculate per tonne (all required data is available)
                                                $oldest = $trip->job->order->oldest ?? null;
                                                if ($oldest && isset($oldest->total_kg) && $oldest->total_kg > 0) {
                                                    // Convert kg to tonnes and calculate fee per tonne
                                                    $tonnes = $oldest->total_kg / 1000;
                                                    $feeAmount = $transport->amount / $tonnes;
                                                } else {
                                                    // If total_kg is not available, use the full amount
                                                    $feeAmount = $transport->amount;
                                                }
                                                
                                                // Convert cents to MYR
                                                $feeAmount = $feeAmount / 100;
                                            }
                                        @endphp
                                        {{ 'MYR ' . number_format($feeAmount, 2) }}
                                    </td>
                                    <td>
                                        @if($transport && 
                                            optional($transport->order_amountable)->route &&
                                            optional($transport->order_amountable->route)->distance_text)
                                            {{ $transport->order_amountable->route->distance_text }}
                                        @elseif($trip->distance_km)
                                            {{ number_format($trip->distance_km, 1) . ' km' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($transport && 
                                            optional($transport->order_amountable)->route)
                                            {{ optional($transport->order_amountable->route)->traffic_text ?? 
                                               optional($transport->order_amountable->route)->duration_text ?? 
                                               'N/A' }}
                                        @elseif($trip->duration_minutes)
                                            {{ $trip->duration_minutes . ' mins' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $trip->created_at ? $trip->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                                    <td>
                                        @php
                                            $status = $trip->status ?? 'Unknown';
                                            $statusClass = 'bg-info-focus text-info-600 border border-info-main';
                                            
                                            if($status == 'Completed') {
                                                $statusClass = 'bg-success-focus text-success-600 border border-success-main';
                                            } elseif(in_array($status, ['Ongoing', 'Started', 'Loading', 'Loaded', 'Unloading', 'Arriving', 'Arrived', 'Notified', 'Waiting', 'Confirmed'])) {
                                                $statusClass = 'bg-warning-focus text-warning-600 border border-warning-main';
                                            } elseif($status == 'Assigned') {
                                                $statusClass = 'bg-primary-focus text-primary-600 border border-primary-main';
                                            } elseif(in_array($status, ['Cancelled', 'Terminated'])) {
                                                $statusClass = 'bg-danger-focus text-danger-600 border border-danger-main';
                                            }
                                        @endphp
                                        <span class="{{ $statusClass }} px-24 py-4 radius-4 fw-medium text-sm">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td>{{ $trip->updated_at ? $trip->updated_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-10 justify-content-center">
                                            <a href="{{ route('tripDetails', $trip->id) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View Details">
                                                <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                            </a>
                                            @if(in_array($trip->status, ['Completing', 'Completed']) && $trip->latest && $trip->latest->delivery_proof)
                                                <a href="{{ $trip->latest->delivery_proof }}" target="_blank" class="bg-success-focus bg-hover-success-200 text-success-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View Delivery Proof">
                                                    <iconify-icon icon="mdi:file-image-outline" class="icon text-xl"></iconify-icon>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="21" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                            <iconify-icon icon="mdi:truck-outline" class="icon text-6xl text-neutral-400 mb-3"></iconify-icon>
                                            <h5 class="text-neutral-500 mb-2">No Trips Found</h5>
                                            <p class="text-neutral-400 mb-0">
                                                @if(request('search') || request('status') != 'All Status' || request('created_start_date') || request('created_end_date') || request('updated_start_date') || request('updated_end_date'))
                                                    No trips match your filter criteria.
                                                @else
                                                    There are no trips in the system yet.
                                                @endif
                                            </p>
                                            <!-- Only show this in development environments -->
                                            @if(config('app.env') == 'local')
                                            <div class="mt-3 text-secondary-light">
                                                <p>Debug Info:</p>
                                                <ul class="text-start">
                                                    <li>Per Page: {{ request('per_page') ?? 'null' }}</li>
                                                    <li>Status: {{ request('status') ?? 'null' }}</li>
                                                    <li>Search: {{ request('search') ?? 'null' }}</li>
                                                    <li>Created Date: {{ request('created_start_date') ?? 'null' }} to {{ request('created_end_date') ?? 'null' }}</li>
                                                    <li>Updated Date: {{ request('updated_start_date') ?? 'null' }} to {{ request('updated_end_date') ?? 'null' }}</li>
                                                </ul>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                        <span>
                            {{ $trips->firstItem() ? $trips->firstItem() : 0 }}-{{ $trips->lastItem() ? $trips->lastItem() : 0 }} of {{ $trips->total() }}
                            @if(request('search') || (request('status') && request('status') != 'All Status') || request('created_start_date') || request('created_end_date') || request('updated_start_date') || request('updated_end_date'))
                                (filtered)
                                @if(request('created_start_date') || request('created_end_date'))
                                    <span class="badge bg-info text-white rounded-pill">Created</span>
                                @endif
                                @if(request('updated_start_date') || request('updated_end_date'))
                                    <span class="badge bg-info text-white rounded-pill">Updated</span>
                                @endif
                            @endif
                        </span>
                        
                        @if ($trips->hasPages())
                            <nav aria-label="Trips pagination">
                                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                                    {{-- Previous Page Link --}}
                                    @if ($trips->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $trips->appends(request()->except('page'))->previousPageUrl() }}">
                                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $currentPage = $trips->currentPage();
                                        $lastPage = $trips->lastPage();
                                        $window = 2; // Show 2 pages before and after the current page
                                        
                                        // Calculate start and end pages for the window
                                        $startPage = max(1, $currentPage - $window);
                                        $endPage = min($lastPage, $currentPage + $window);
                                        
                                        // Adjust for ellipsis
                                        $ellipsisStart = false;
                                        $ellipsisEnd = false;
                                        
                                        // Check if we need starting ellipsis
                                        if ($startPage > 1) {
                                            $ellipsisStart = true;
                                        }
                                        
                                        // Check if we need ending ellipsis
                                        if ($endPage < $lastPage) {
                                            $ellipsisEnd = true;
                                        }
                                        
                                        // Get page links for the window
                                        $links = $trips->appends(request()->except('page'))->getUrlRange($startPage, $endPage);
                                    @endphp
                                    
                                    {{-- First Page if not in range --}}
                                    @if($ellipsisStart && $startPage > 1)
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $trips->appends(request()->except('page'))->url(1) }}">1</a>
                                        </li>
                                        @if($startPage > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">...</span>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    {{-- Page Numbers in the window --}}
                                    @foreach ($links as $page => $url)
                                        @if ($page == $trips->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                    
                                    {{-- Last Page if not in range --}}
                                    @if($ellipsisEnd && $endPage < $lastPage)
                                        @if($endPage < $lastPage - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $trips->appends(request()->except('page'))->url($lastPage) }}">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($trips->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $trips->appends(request()->except('page'))->nextPageUrl() }}">
                                                <iconify-icon icon="ep:d-arrow-right"></iconify-icon>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                                                <iconify-icon icon="ep:d-arrow-right"></iconify-icon>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>

@endsection