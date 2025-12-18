@extends('layout.layout')
@php
$title='Customer Bank Accounts';
$subTitle = 'Customer Bank Accounts List';
$script ='<script>
    $(document).ready(function() {
        $(".copy-text").on("click", function() {
            var text = $(this).data("clipboard-text");
            navigator.clipboard.writeText(text);

            // Show notification
            var notification = $("<div>")
                .addClass("notification-toast")
                .text("CustomerAccount id copied")
                .appendTo("body");

            setTimeout(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 1500);
        });
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
                    <option value="Status" {{ request('status') == 'Status' || !request('status') ? 'selected' : '' }}>Status</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Reject" {{ request('status') == 'Reject' ? 'selected' : '' }}>Reject</option>
                </select>
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        <a href="{{ route('customer-accounts.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Customer Account
        </a>
    </div>
    <div class="card-body p-24">
        @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
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
                        <th scope="col">Account Name</th>
                        <th scope="col">Account Number</th>
                        <th scope="col">Bank</th>
                        <th scope="col">Customer</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerAccounts as $index => $account)
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
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $account->name }}</span></td>
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $account->number }}</span></td>
                        <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $account->bank }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if(optional($account->customer)->profile_photo_path)
                                <img src="{{ asset('storage/' . $account->customer->profile_photo_path) }}" alt="{{ $account->customer->name }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                @else
                                <img src="{{ asset('assets/images/user.png') }}" alt="{{ optional($account->customer)->name ?? 'N/A' }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                @endif
                                <div class="flex-grow-1">
                                    <span class="text-md mb-0 fw-normal text-secondary-light">{{ optional($account->customer)->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                            $status = $account->status;
                            $statusClass = '';
                            $textClass = '';
                            $borderClass = '';

                            if ($status == 'Approved') {
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
                            <span class="{{ $statusClass }} {{ $textClass }} border {{ $borderClass }} px-24 py-4 radius-4 fw-medium text-sm">{{ $status }}</span>
                        </td>
                        <td>{{ $account->created_at ? (is_string($account->created_at) ? $account->created_at : $account->created_at->format('d M Y')) : 'N/A' }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('customer-accounts.show', $account) }}" class="bg-info-focus bg-hover-info-200 text-info-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                    <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                </a>
                                <a href="{{ route('customer-accounts.edit', $account) }}" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Edit">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </a>
                                @if($account->document)
                                <a href="{{ route('customer-accounts.document', $account) }}" target="_blank" class="bg-primary-focus text-primary-600 bg-hover-primary-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="Bank Statement">
                                    <iconify-icon icon="mdi:file-document-outline" class="menu-icon"></iconify-icon>
                                </a>
                                @endif
                                <!-- <form action="{{ route('customer-accounts.destroy', $account) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this customer account?')">
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
                        <td colspan="8" class="text-center">No customer accounts found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                Showing {{ $customerAccounts->firstItem() ?? 0 }} to {{ $customerAccounts->lastItem() ?? 0 }} of {{ $customerAccounts->total() }} entries
            </span>

            @if ($customerAccounts->hasPages())
            <nav aria-label="Customer Accounts pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($customerAccounts->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $customerAccounts->previousPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($customerAccounts->getUrlRange(1, $customerAccounts->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $customerAccounts->currentPage() ? 'active' : '' }}">
                        <a class="page-link {{ $page == $customerAccounts->currentPage() ? 'bg-primary-600 text-white' : 'bg-neutral-200 text-secondary-light' }} fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $url }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($customerAccounts->hasMorePages())
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $customerAccounts->nextPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}&status={{ urlencode(request('status')) }}">
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

<style>
    .notification-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border-radius: 4px;
        z-index: 9999;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
</style>
@endsection