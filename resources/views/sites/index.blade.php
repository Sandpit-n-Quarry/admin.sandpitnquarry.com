@extends('layout.layout')
@php
$title='Quarries List';
$subTitle = 'Quarry Management';
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
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search quarries..." value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon" onclick="document.getElementById('search-form').submit()"></iconify-icon>
                @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <button type="submit" class="d-none"></button>
            </form>
        </div>
        <a href="{{ route('sites.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Quarry
        </a>
    </div>
    <div class="card-body p-24">
        @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
        @endif

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
                        <th scope="col">Name</th>
                        <th scope="col">Address</th>
                        <th scope="col">Postcode</th>
                        <th scope="col">City</th>
                        <th scope="col">State</th>
                        <th scope="col">Latitue</th>
                        <th scope="col">Longitude</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Created at</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $index => $site)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $site->id }}">
                                </div>
                                {{ str_pad($sites->firstItem() + $index, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $site->name }}</span></td>
                        <td>{{ $site->address }}</td>
                        <td>{{ $site->postcode }}</td>
                        <td>{{ $site->city ?? 'N/A' }}</td>
                        <td>{{ $site->state?? 'N/A' }}</td>
                        <td>{{ $site->latitude ?? 'N/A' }}</td>
                        <td>{{ $site->longitude ?? 'N/A' }}</td>
                        <td>{{ $site->phone }}</td>
                        <td>{{ $site->created_at->format('d M Y') }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('sites.show', $site->id) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                </a>
                                <a href="{{ route('sites.edit', $site->id) }}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Edit">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                <!-- <form action="{{ route('sites.destroy', $site->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this quarry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle border-0" title="Delete">
                                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                    </button>
                                </form> -->
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                <iconify-icon icon="mdi:map-marker-off" class="icon text-6xl text-neutral-400 mb-3"></iconify-icon>
                                <h5 class="text-neutral-500 mb-2">No Quarries Found</h5>
                                <p class="text-neutral-400 mb-0">
                                    @if(request('search'))
                                    No quarries match your search criteria.
                                    @else
                                    There are no quarries in the system yet.
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
            @if($sites->count() > 0)
            <span>
                Showing {{ $sites->firstItem() }} to {{ $sites->lastItem() }} of {{ $sites->total() }} entries
            </span>
            @endif

            @if ($sites->hasPages())
            <nav aria-label="Quarry pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($sites->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $sites->previousPageUrl() }}{{ request('per_page') ? '&per_page='.request('per_page') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($sites->getUrlRange(1, $sites->lastPage()) as $page => $url)
                    @if ($page == $sites->currentPage())
                    <li class="page-item active">
                        <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $url }}{{ request('per_page') ? '&per_page='.request('per_page') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}">{{ $page }}</a>
                    </li>
                    @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($sites->hasMorePages())
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $sites->nextPageUrl() }}{{ request('per_page') ? '&per_page='.request('per_page') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}">
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