@extends('layout.layout')
@php
$title='Accounts';
$subTitle = 'Accounts List';
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
                    <option value="Approve" {{ request('status') == 'Approve' ? 'selected' : '' }}>Approve</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Reject" {{ request('status') == 'Reject' ? 'selected' : '' }}>Reject</option>
                </select>
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Account
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
                        <th scope="col">Code</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Term</th>
                        <th scope="col">Limit</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Created At</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $index => $account)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="form-check style-check d-flex align-items-center">
                                    <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $account->id }}">
                                </div>
                                <span class="copy-text" data-clipboard-text="{{ $account->id }}">
                                    {{ $account->id }}
                                </span>
                            </div>
                        </td>
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($account->latest)->code ?? 'N/A' }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if(optional($account->user)->profile_photo_path)
                                <img src="{{ asset('storage/' . $account->user->profile_photo_path) }}" alt="{{ $account->user->name }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                @else
                                <img src="{{ asset('assets/images/user.png') }}" alt="{{ optional($account->user)->name ?? 'N/A' }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                @endif
                                <div class="flex-grow-1">
                                    <span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($account->user)->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-md mb-0 fw-normal text-secondary-light">
                                @if(optional(optional($account->latest)->latest)->term > 0)
                                {{ $account->latest->latest->term }} days
                                @else
                                Cash
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="text-md mb-0 fw-normal text-secondary-light">
                                @if(optional(optional($account->latest)->latest)->limit > 0)
                                RM {{ number_format($account->latest->latest->limit, 2) }}
                                @else
                                Cash
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                            $status = optional($account->latest)->status;
                            $statusClass = '';
                            $textClass = '';
                            $borderClass = '';

                            if ($status == 'Approve') {
                            $statusClass = 'bg-success-focus';
                            $textClass = 'text-success-600';
                            $borderClass = 'border-success-main';
                            } elseif ($status == 'Pending') {
                            $statusClass = 'bg-warning-focus';
                            $textClass = 'text-warning-600';
                            $borderClass = 'border-warning-main';
                            } elseif ($status == 'Reject') {
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
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($account->latest)->remark ?? 'N/A' }}</span></td>
                        <td>{{ $account->created_at ? $account->created_at->format('d M Y') : 'N/A' }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('accounts.show', $account) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                </a>
                                <a href="{{ route('accounts.edit', $account) }}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Edit">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                <!-- <form action="{{ route('accounts.destroy', $account) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this account?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle border-0" title="Delete">
                                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                    </button>
                                </form> -->
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} entries
            </span>

            @if ($accounts->hasPages())
            <nav aria-label="Accounts pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($accounts->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $accounts->previousPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements with Ellipsis --}}
                    @php
                    $total = $accounts->lastPage();
                    $current = $accounts->currentPage();
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

                            @foreach ($displayPages as $page)
                            @if ($page === '...')
                            <li class="page-item disabled">
                                <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">...</span>
                            </li>
                            @elseif ($page == $accounts->currentPage())
                            <li class="page-item active">
                                <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                            </li>
                            @else
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $accounts->url($page) }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">{{ $page }}</a>
                            </li>
                            @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($accounts->hasMorePages())
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $accounts->nextPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
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
                toast.innerHTML = `
                    <div class="toast show bg-dark text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body d-flex align-items-center">
                            <iconify-icon icon="material-symbols:check-circle" class="text-success me-2" width="20"></iconify-icon>
                            <div>Account ID copied to clipboard!</div>
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