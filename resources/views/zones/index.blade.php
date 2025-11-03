@extends('layout.layout')
@php
$title='Zones List';
$subTitle = 'Zone Management';
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
            </form>
            <form class="navbar-search" method="GET" id="search-form">
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search zones..." value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon" onclick="document.getElementById('search-form').submit()"></iconify-icon>
                @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <button type="submit" class="d-none"></button>
            </form>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="POST" action="{{ route('zones.create') }}" class="d-flex align-items-center gap-2">
                @csrf
                <input type="text" name="name" class="form-control form-control-sm" placeholder="Zone Name" required style="width: 140px;">
                <input type="text" name="state" class="form-control form-control-sm" placeholder="State" required style="width: 100px;">
                <button type="submit" class="btn btn-primary btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                    <iconify-icon icon="mdi:plus-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                    New Zone
                </button>
            </form>
        </div>
    </div>

    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0 table-hover">
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
                        <th scope="col">Name</th>
                        <th scope="col">State</th>
                        <th scope="col">Postcode</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($zones as $index => $zone)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $zone->id }}">
                                </div>
                                {{ $zone->id }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-md mb-0 fw-medium text-secondary-light">{{ $zone->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $zone->state ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex justify-content-between align-items-start w-100">
                                <div>
                                    @if(isset($zonePostcodes[$zone->id]) && count($zonePostcodes[$zone->id]) > 0)
                                        <ul class="mb-0 ps-3">
                                            @foreach($zonePostcodes[$zone->id] as $postcode)
                                                <li class="mb-1">{{ $postcode }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div>
                                    <form method="POST" action="{{ route('zones.postcodes.add') }}" class="d-flex align-items-center gap-1">
                                        @csrf
                                        <input type="hidden" name="zone_id" value="{{ $zone->id }}">
                                        <select class="form-select form-select-sm" name="postcodes" required style="width: 110px;">
                                            <option value="" disabled selected>Select</option>
                                            @foreach(($allPostcodes ?? []) as $postcode)
                                                @if(!empty($postcode))
                                                    <option value="{{ $postcode }}">{{ $postcode }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-xs btn-outline-success ms-2" title="Add Postcode">
                                            <iconify-icon icon="mdi:plus-circle-outline" class="text-success"></iconify-icon>
                                            Add
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No zones available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                Showing {{ $zones->firstItem() }} to {{ $zones->lastItem() }} of {{ $zones->total() }} entries
            </span>

            @if ($zones->hasPages())
            <nav aria-label="Zone pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($zones->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $zones->previousPageUrl() }}{{ request('per_page') ? '&per_page='.request('per_page') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements with Ellipsis --}}
                    @php
                    $total = $zones->lastPage();
                    $current = $zones->currentPage();
                    $delta = 2;
                    $pages = [];
                    for ($i = 1; $i <= $total; $i++) {
                        if ($i==1 || $i==$total || ($i>= $current - $delta && $i <= $current + $delta)) {
                            $pages[]=$i;
                            }
                            }
                            $displayPages=[];
                            $prev=0;
                            foreach ($pages as $page) {
                            if ($prev && $page - $prev> 1) {
                            $displayPages[] = '...';
                            }
                            $displayPages[] = $page;
                            $prev = $page;
                            }
                            @endphp

                            {{-- Pagination Elements with Ellipsis --}}
                            @foreach ($displayPages as $page)
                            @if ($page === '...')
                            <li class="page-item disabled">
                                <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">...</span>
                            </li>
                            @elseif ($page == $zones->currentPage())
                            <li class="page-item active">
                                <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                            </li>
                            @else
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $zones->url($page) }}{{ request('per_page') ? '&per_page='.request('per_page') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}">{{ $page }}</a>
                            </li>
                            @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($zones->hasMorePages())
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $zones->nextPageUrl() }}{{ request('per_page') ? '&per_page='.request('per_page') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}">
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






@if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
@elseif(session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif

@endsection