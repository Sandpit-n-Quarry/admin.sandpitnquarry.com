@extends('layout.layout')
@php
$title='Orders';
$subTitle = 'Orders Management';
$script ='<script>
    $(".remove-item-btn").on("click", function() {
        $(this).closest("tr").addClass("d-none")
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
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search orders..." value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon" onclick="document.getElementById('search-form').submit()"></iconify-icon>
                @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <button type="submit" class="d-none"></button>
            </form>
            <form method="GET" id="status-form">
                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="status" onchange="document.getElementById('status-form').submit()">
                    <option value="All Status" {{ request('status') === 'All Status' ? 'selected' : '' }}>All Status</option>
                    @foreach($orderStatuses as $status)
                    <option value="{{ $status->name }}" {{ request('status') === $status->name ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
                @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
            </form>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ordersExport', request()->all()) }}" class="btn btn-success text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                <iconify-icon icon="material-symbols:download" class="icon text-xl line-height-1"></iconify-icon>
                Export to Excel
            </a>
            <a href="{{ route('orderStatuses') }}" class="btn btn-outline-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                <iconify-icon icon="mdi:format-list-bulleted" class="icon text-xl line-height-1"></iconify-icon>
                Order Statuses
            </a>
            <a href="{{ route('freeDeliveries') }}" class="btn btn-outline-success text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                <iconify-icon icon="mdi:truck-delivery" class="icon text-xl line-height-1"></iconify-icon>
                Free Deliveries
            </a>
            <a href="{{ route('selfPickups') }}" class="btn btn-outline-info text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                <iconify-icon icon="mdi:store" class="icon text-xl line-height-1"></iconify-icon>
                Self Pickups
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
                        <th scope="col">PO/PI</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Quarry / Site</th>
                        <th scope="col">Product</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Unit</th>
                        <th scope="col">Price / Unit</th>
                        <th scope="col">Tonne</th>
                        <th scope="col">Fee</th>
                        <th scope="col">Distance</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Wheel</th>
                        <th scope="col">Start at</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Completed</th>
                        <th scope="col">Ongoing</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created at</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $index => $order)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $order->id }}">
                                </div>
                                #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>
                        <td>{{ optional($order->purchase)->id ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-md mb-0 fw-normal text-secondary-light">{{ $order->customer->name ?? 'N/A' }}</span>
                                    <br>
                                    <small class="text-xs text-secondary-light">{{ $order->customer->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ optional($order->latest->site)->name ?? optional($order->latest->site)->merchant->name ?? optional($order->latest->site)->name ?? 'N/A' }}</td>
                        <td>{{ optional($order->product)->name ?? 'N/A' }}</td>
                        <td>{{ optional($order->creator)->name ?? 'N/A' }}</td>
                        <td>{{ $order->unit ?? 'N/A' }}</td>
                        <td>{{ isset($order->price_per_unit) ? 'MYR '.number_format($order->price_per_unit,2) : (isset($order->cost_amount) ? 'MYR '.number_format($order->cost_amount,2) : 'N/A') }}</td>
                        <td>{{ $order->oldest->quantity ?? ($order->order_details->sum('quantity') ?? 'N/A') }}</td>
                        <td>
                            @php
                            $transport = $order->transportation_amount;
                            $feeAmount = 0;

                            if (!is_null($transport) && isset($transport->amount) && !is_null($order->oldest) && isset($order->oldest->total_kg) && $order->oldest->total_kg > 0) {
                            $feeAmount = $transport->amount / ($order->oldest->total_kg / 1000);
                            }
                            @endphp
                            {{ 'MYR ' . number_format($feeAmount, 2) }}
                        </td>
                        <td>
                            @if($transport &&
                            optional($transport->order_amountable)->route &&
                            optional($transport->order_amountable->route)->distance_text)
                            {{ $transport->order_amountable->route->distance_text }}
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
                            @else
                            N/A
                            @endif
                        </td>
                        <td>{{ optional($order->wheel)->wheel ?? 'N/A' }}</td>
                        <td>{{ optional($order->purchase)->created_at ? $order->purchase->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                        <td>{{ $order->latest->total ?? ($order->order_details->sum('quantity') ?? 'N/A') }}</td>
                        <td>{{ $order->completed ?? 0 }}</td>
                        <td>{{ $order->ongoing ?? 0 }}</td>
                        <td>
                            @if($order->orderStatus)
                            <span class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm">{{ $order->orderStatus->name }}</span>
                            @else
                            <span class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm">Unknown</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at ? $order->created_at->format('d M Y H:i') : 'N/A' }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('orderDetails', $order->id) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View Details">
                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                <iconify-icon icon="mdi:cart-outline" class="icon text-6xl text-neutral-400 mb-3"></iconify-icon>
                                <h5 class="text-neutral-500 mb-2">No Orders Found</h5>
                                <p class="text-neutral-400 mb-0">
                                    @if(request('search'))
                                    No orders match your search criteria.
                                    @else
                                    There are no orders in the system yet.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                Showing {{ $orders->firstItem() ? $orders->firstItem() : 0 }} to {{ $orders->lastItem() ? $orders->lastItem() : 0 }} of {{ $orders->total() }} entries
                @if(request('search') || (request('status') && request('status') != 'All Status'))
                (filtered from {{ $orders->total() }} total entries)
                @endif
            </span>

            @if ($orders->hasPages())
            <nav aria-label="Orders pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($orders->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $orders->appends(request()->except('page'))->previousPageUrl() }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @php
                    $currentPage = $orders->currentPage();
                    $lastPage = $orders->lastPage();
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
                        $ellipsisEnd=true;
                        }

                        // Get page links for the window
                        $links=$orders->appends(request()->except('page'))->getUrlRange($startPage, $endPage);
                        @endphp

                        {{-- First Page if not in range --}}
                        @if($ellipsisStart && $startPage > 1)
                        <li class="page-item">
                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $orders->appends(request()->except('page'))->url(1) }}">1</a>
                        </li>
                        @if($startPage > 2)
                        <li class="page-item disabled">
                            <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">...</span>
                        </li>
                        @endif
                        @endif

                        {{-- Page Numbers in the window --}}
                        @foreach ($links as $page => $url)
                        @if ($page == $orders->currentPage())
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
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $orders->appends(request()->except('page'))->url($lastPage) }}">{{ $lastPage }}</a>
                            </li>
                            @endif

                            {{-- Next Page Link --}}
                            @if ($orders->hasMorePages())
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $orders->appends(request()->except('page'))->nextPageUrl() }}">
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