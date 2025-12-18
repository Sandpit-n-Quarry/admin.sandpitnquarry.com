@extends('layout.layout')
@php
$title='Transporter Withdrawals';
$subTitle = 'Transporter Withdrawals';
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
            <form class="navbar-search" method="GET">
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search" value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
            </form>
        </div>
    </div>
    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Transporter</th>
                        <th>Account Holder Name</th>
                        <th>Account Holder Number</th>
                        <th>Bank</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr>
                            <td>{{ $claim->id }}</td>
                            <td>{{ $claim->customer_account->customer->transporter->name ?? '' }}</td>
                            <td>{{ $claim->customer_account->name ?? '' }}</td>
                            <td>{{ $claim->customer_account->number ?? '' }}</td>
                            <td>{{ $claim->customer_account->bank ?? '' }}</td>
                            <td>{{ $claim->amount }}</td>
                            <td>
                                @php
                                    $status = $claim->latest?->status;
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
                                <span class="{{ $statusClass }} {{ $textClass }} border {{ $borderClass }} px-24 py-4 radius-4 fw-medium text-sm">{{ $status ?? '' }}</span>
                            </td>
                            <td>
                                @if($status == 'Pending')
                                    <form method="POST" action="{{ route('transporter-withdrawals.update-status', $claim->id) }}">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm w-auto d-inline-block" onchange="this.form.submit()">
                                            <option value="Pending" selected>Pending</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Reject">Reject</option>
                                        </select>
                                    </form>
                                @else
                                    <select class="form-select form-select-sm w-auto d-inline-block" disabled>
                                        <option selected>{{ $status }}</option>
                                    </select>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No withdrawals found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                Showing {{ $claims->firstItem() ?? 0 }} to {{ $claims->lastItem() ?? 0 }} of {{ $claims->total() }} entries
            </span>
            @if ($claims->hasPages())
            <nav aria-label="Withdrawals pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($claims->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $claims->previousPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}">
                            <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                        </a>
                    </li>
                    @endif
                    @php
                        $total = $claims->lastPage();
                        $current = $claims->currentPage();
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
                        @elseif ($page == $claims->currentPage())
                        <li class="page-item active">
                            <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                        </li>
                        @else
                        <li class="page-item">
                            <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                href="{{ $claims->url($page) }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}">{{ $page }}</a>
                        </li>
                        @endif
                    @endforeach
                    {{-- Next Page Link --}}
                    @if ($claims->hasMorePages())
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $claims->nextPageUrl() }}&per_page={{ request('per_page', 10) }}&search={{ urlencode(request('search')) }}">
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
