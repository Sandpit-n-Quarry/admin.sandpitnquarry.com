@extends('layout.layout')
@php
$title='Drivers';
$subTitle = 'Drivers List';
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
            <form method="GET">
                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="per_page" onchange="this.form.submit()">
                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
            </form>
            <form class="navbar-search" method="GET">
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search" value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
            <form method="GET">
                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="status" onchange="this.form.submit()">
                    <option value="Status" {{ request('status') == 'Status' ? 'selected' : '' }}>Status</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="Suspended" {{ request('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Driver
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
                        <th scope="col">Driver</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Transporter</th>
                        <th scope="col">Truck</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $index => $driver)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $driver->id }}">
                                </div>
                                <span class="copy-text" data-clipboard-text="{{ $driver->id }}">
                                    {{ $driver->id }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if(optional($driver->user)->profile_photo_path)
                                <img src="{{ asset('storage/' . $driver->user->profile_photo_path) }}" alt="{{ $driver->user->name }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                @else
                                <img src="{{ asset('assets/images/user.png') }}" alt="{{ optional($driver->user)->name ?? 'N/A' }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                @endif
                                <div class="flex-grow-1">
                                    <span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($driver->user)->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="copy-text" data-clipboard-text="{{ optional($driver->user)->phone }}">
                                <span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($driver->user)->phone ?: 'N/A' }}</span>
                            </span>
                        </td>
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($driver->transporter)->name ?? 'N/A' }}</span></td>
                        <td>
                            @if(optional(optional($driver->current)->truck)->registration_plate_number)
                            <span class="copy-text" data-clipboard-text="{{ $driver->current->truck->registration_plate_number }}">
                                <span class="text-md mb-0 fw-normal text-secondary-light">{{ $driver->current->truck->registration_plate_number }}</span>
                            </span>
                            @else
                            <span class="text-md mb-0 fw-normal text-secondary-light">N/A</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                            $status = optional($driver->latest)->status;
                            $statusClass = '';
                            $textClass = '';
                            $borderClass = '';

                            if ($status == 'Active') {
                            $statusClass = 'bg-success-focus';
                            $textClass = 'text-success-600';
                            $borderClass = 'border-success-main';
                            } elseif ($status == 'Inactive') {
                            $statusClass = 'bg-warning-focus';
                            $textClass = 'text-warning-600';
                            $borderClass = 'border-warning-main';
                            } elseif ($status == 'Suspended') {
                            $statusClass = 'bg-danger-focus';
                            $textClass = 'text-danger-600';
                            $borderClass = 'border-danger-main';
                            } else {
                            $statusClass = 'bg-info-focus';
                            $textClass = 'text-info-600';
                            $borderClass = 'border-info-main';
                            }
                            @endphp
                            <span class="{{ $statusClass }} {{ $textClass }} border {{ $borderClass }} px-24 py-4 radius-4 fw-medium text-sm">{{ $status ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $driver->created_at ? $driver->created_at->format('d M Y') : 'N/A' }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('drivers.show', $driver) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                </a>
                                <a href="{{ route('drivers.edit', $driver) }}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Edit">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                <!-- <form action="{{ route('drivers.destroy', $driver) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this driver?')">
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
                        <td colspan="8" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                                <iconify-icon icon="mdi:truck-driver" class="icon text-6xl text-neutral-400 mb-3"></iconify-icon>
                                <h5 class="text-neutral-500 mb-2">No Drivers Found</h5>
                                <p class="text-neutral-400 mb-0">
                                    @if(request('search'))
                                    No drivers match your search criteria.
                                    @else
                                    There are no drivers in the system yet.
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
                Showing {{ $drivers->firstItem() ?? 0 }} to {{ $drivers->lastItem() ?? 0 }} of {{ $drivers->total() }} entries
            </span>

            @if ($drivers->hasPages())
            <nav aria-label="Drivers pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($drivers->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $drivers->previousPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements with Ellipsis --}}
                    @php
                    $total = $drivers->lastPage();
                    $current = $drivers->currentPage();
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
                            @elseif ($page == $drivers->currentPage())
                            <li class="page-item active">
                                <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                            </li>
                            @else
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $drivers->url($page) }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">{{ $page }}</a>
                            </li>
                            @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($drivers->hasMorePages())
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $drivers->nextPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkboxes functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="checkbox"]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }

        // Copy to clipboard functionality
        const copyElements = document.querySelectorAll('.copy-text');
        copyElements.forEach(function(element) {
            element.addEventListener('click', function() {
                const text = this.getAttribute('data-clipboard-text');
                navigator.clipboard.writeText(text);

                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 p-3';
                toast.style.zIndex = 1070;

                let toastMessage = 'Copied to clipboard!';

                // Determine what kind of information was copied
                if (text.length === 10 && /^\d+$/.test(text)) {
                    toastMessage = 'Phone number copied!';
                } else if (text.match(/^[A-Z0-9]{3,7}$/)) {
                    toastMessage = 'Truck plate number copied!';
                } else {
                    toastMessage = 'Driver ID copied!';
                }

                toast.innerHTML = `
                    <div class="toast show bg-dark text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body d-flex align-items-center">
                            <iconify-icon icon="material-symbols:check-circle" class="text-success me-2" width="20"></iconify-icon>
                            <div>${toastMessage}</div>
                        </div>
                    </div>
                `;

                document.body.appendChild(toast);

                // Remove after 2 seconds
                setTimeout(() => {
                    toast.remove();
                }, 2000);
            });
        });
    });
</script>
@endpush
@endsection