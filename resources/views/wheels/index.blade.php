@extends('layout.layout')
@php
    $title='Wheels';
    $subTitle = 'Wheels List';
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
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                        </form>
                        <form class="navbar-search" method="GET">
                            <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search" value="{{ request('search') }}">
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        </form>
                        <form method="GET">
                            <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="filter" onchange="this.form.submit()">
                                <option value="" {{ request('filter') == '' ? 'selected' : '' }}>All Wheels</option>
                                <option value="load" {{ request('filter') == 'load' ? 'selected' : '' }}>Load Only</option>
                                <option value="tonne" {{ request('filter') == 'tonne' ? 'selected' : '' }}>Tonne Only</option>
                                <option value="both" {{ request('filter') == 'both' ? 'selected' : '' }}>Load & Tonne</option>
                            </select>
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        </form>
                    </div>
                    <a href="{{ route('wheels.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Add New Wheel
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
                                            Wheel
                                        </div>
                                    </th>
                                    <th scope="col">Capacity</th>
                                    <th scope="col" class="text-center">Load</th>
                                    <th scope="col" class="text-center">Tonne</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wheels as $wheel)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-10">
                                                <div class="form-check style-check d-flex align-items-center">
                                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $wheel->wheel }}">
                                                </div>
                                                <span class="copy-text" data-clipboard-text="{{ $wheel->wheel }}">
                                                    {{ $wheel->wheel }}
                                                </span>
                                            </div>
                                        </td>
                                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $wheel->capacity }}</span></td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input toggle-wheel-property" data-id="{{ $wheel->id }}" data-property="load" type="checkbox" {{ $wheel->load ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input toggle-wheel-property" data-id="{{ $wheel->id }}" data-property="tonne" type="checkbox" {{ $wheel->tonne ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>{{ $wheel->created_at ? $wheel->created_at->format('d M Y') : 'N/A' }}</td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                                <a href="{{ route('wheels.show', $wheel) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                                </a>
                                                <a href="{{ route('wheels.edit', $wheel) }}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Edit">
                                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                                </a>
                                                <!-- <form action="{{ route('wheels.destroy', $wheel) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this wheel?')">
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
                                        <td colspan="6" class="text-center">No wheels found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                        <span>
                            Showing {{ $wheels->firstItem() ?? 0 }} to {{ $wheels->lastItem() ?? 0 }} of {{ $wheels->total() }} entries
                        </span>
                        
                        @if ($wheels->hasPages())
                            <nav aria-label="Wheels pagination">
                                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                                    {{-- Previous Page Link --}}
                                    @if ($wheels->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $wheels->previousPageUrl() }}">
                                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($wheels->getUrlRange(1, $wheels->lastPage()) as $page => $url)
                                        <li class="page-item {{ $page == $wheels->currentPage() ? 'active' : '' }}">
                                            <a class="page-link {{ $page == $wheels->currentPage() ? 'bg-primary-600 text-white' : 'bg-neutral-200 text-secondary-light' }} fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $url }}">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($wheels->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $wheels->nextPageUrl() }}">
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Select all checkboxes
        $('#selectAll').on('change', function() {
            $('input[name="checkbox"]').prop('checked', $(this).prop('checked'));
        });
        
        // Handle toggle for load and tonne properties
        $('.toggle-wheel-property').on('change', function() {
            const wheelId = $(this).data('id');
            const property = $(this).data('property');
            const isChecked = $(this).prop('checked') ? 1 : 0;
            const $checkbox = $(this);
            
            // Show loading indicator or disable temporarily to prevent multiple clicks
            $checkbox.prop('disabled', true);
            
            $.ajax({
                url: "{{ route('wheels.toggle-property') }}", // We'll create this route
                method: 'POST',
                data: {
                    id: wheelId,
                    property: property,
                    value: isChecked,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    // Display success notification if needed
                    if (response.success) {
                        // Optional: show a success message
                        const successMsg = $('<div class="alert alert-success mb-4">' + response.message + '</div>');
                        $('.card-body').prepend(successMsg);
                        setTimeout(() => successMsg.fadeOut('slow', function() { $(this).remove(); }), 3000);
                    }
                },
                error: function(xhr) {
                    // Revert checkbox state on error
                    $checkbox.prop('checked', !isChecked);
                    
                    // Show error message
                    const errorMsg = $('<div class="alert alert-danger mb-4">Failed to update wheel property: ' + 
                        (xhr.responseJSON?.message || 'Unknown error') + '</div>');
                    $('.card-body').prepend(errorMsg);
                    setTimeout(() => errorMsg.fadeOut('slow', function() { $(this).remove(); }), 3000);
                },
                complete: function() {
                    // Re-enable the checkbox
                    $checkbox.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush