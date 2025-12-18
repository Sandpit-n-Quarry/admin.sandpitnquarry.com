@extends('layout.layout')
@php
    $title='Payments';
    $subTitle = 'Payments List';
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
                        </form>
                        <span class="text-md fw-medium text-secondary-light mb-0">entries</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="position-relative">
                            <form method="GET">
                                <input type="text" class="form-control min-w-250 h-40-px ps-12 radius-8" name="search" value="{{ request('search') }}" placeholder="Search..." aria-label="search here">
                                <iconify-icon icon="ri:search-line" class="position-absolute text-neutral-400 text-xl top-50 end-0 translate-middle-y me-12"></iconify-icon>
                            </form>
                        </div>
                        <button type="button" class="btn btn-neutral-200 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                            <iconify-icon icon="iconamoon:filter-duotone" class="icon text-xl line-height-1"></iconify-icon>
                            Filter
                        </button>
                        <button type="button" class="btn btn-neutral-200 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                            <iconify-icon icon="ph:export-light" class="icon text-xl line-height-1"></iconify-icon>
                            Export
                        </button>
                    </div>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Add New Payment
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
                                    <th scope="col">Reference Number</th>
                                    <th scope="col">Remark</th>
                                    <th scope="col">Paid At</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $index => $payment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-10">
                                                <div class="form-check style-check d-flex align-items-center">
                                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $payment->id }}">
                                                </div>
                                                <span class="copy-text" data-clipboard-text="{{ $payment->id }}">
                                                    {{ $payment->id }}
                                                </span>
                                            </div>
                                        </td>
                                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $payment->reference_number }}</span></td>
                                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $payment->remark }}</span></td>
                                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($payment->paid_at)->format('Y-m-d H:i') ?? 'N/A' }}</span></td>
                                        <td class="text-center">
                                            @php
                                                $status = optional($payment->latest)->status;
                                                $statusClass = 'badge bg-info';
                                                if ($status === 'Approve') {
                                                    $statusClass = 'badge bg-success';
                                                } elseif ($status === 'Pending') {
                                                    $statusClass = 'badge bg-warning';
                                                } elseif ($status === 'Reject') {
                                                    $statusClass = 'badge bg-danger';
                                                }
                                            @endphp
                                            <span class="{{ $statusClass }}">{{ $status ?? 'N/A' }}</span>
                                        </td>
                                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($payment->created_at)->format('Y-m-d H:i') ?? '-' }}</span></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                                <a href="{{ route('payments.show', $payment) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                                </a>
                                                <a href="{{ route('payments.edit', $payment) }}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Edit">
                                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                                </a>
                                                @if(optional($payment->latest)->status == 'Pending')
                                                    <form action="{{ route('payments.approve', $payment) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle border-0" title="Approve">
                                                            <iconify-icon icon="material-symbols:check" class="menu-icon"></iconify-icon>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('payments.reject', $payment) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="bg-warning-focus text-warning-600 bg-hover-warning-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle border-0" title="Reject">
                                                            <iconify-icon icon="material-symbols:close" class="menu-icon"></iconify-icon>
                                                        </button>
                                                    </form>
                                                @endif
                                                <!-- <form action="{{ route('payments.destroy', $payment) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payment?')">
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
                                        <td colspan="7" class="text-center">No payments found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                        <span>
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                        </span>
                        
                        @if ($payments->hasPages())
                            <nav aria-label="Payments pagination">
                                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                                    {{-- Previous Page Link --}}
                                    @if ($payments->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $payments->previousPageUrl() }}">
                                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                                        @if ($page == $payments->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($payments->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md" href="{{ $payments->nextPageUrl() }}">
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
        // Select all checkbox functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('input[name="checkbox"][type="checkbox"]:not(#selectAll)');
        
        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }
        
        // Copy functionality
        const copyElements = document.querySelectorAll('.copy-text');
        
        copyElements.forEach(function(element) {
            element.addEventListener('click', function() {
                const text = this.getAttribute('data-clipboard-text') || this.textContent.trim();
                navigator.clipboard.writeText(text);
                
                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 p-3';
                toast.style.zIndex = 1070;
                toast.innerHTML = `
                    <div class="toast show bg-dark text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body d-flex align-items-center">
                            <iconify-icon icon="material-symbols:check-circle" class="text-success me-2" width="20"></iconify-icon>
                            <div>Payment ID copied to clipboard!</div>
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