@extends('layout.layout')
@php
$title='Packages';
$subTitle = 'Packages List';
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
            </form>
            <form class="navbar-search" method="GET">
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search" value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        <a href="{{ route('packages.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Package
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
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Period</th>
                            <th scope="col">Service Charge</th>
                            <th scope="col">Payment Term</th>
                            <th scope="col">Delay</th>
                            <th scope="col">Created At</th>
                            <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                    <tr>
                        <td>{{ $package->id }}</td>
                        <td>{{ $package->name }}</td>
                        <td>{{ $package->period ? $package->period . ' months' : 'N/A' }}</td>
                        <td>{{ $package->service_charge ? 'MYR ' . number_format($package->service_charge, 2) : 'N/A' }}</td>
                        <td>{{ $package->payment_term ? $package->payment_term . ' days' : 'N/A' }}</td>
                        <td>
                            @if($package->order_delay_minutes > 0)
                                {{ $package->order_delay_minutes . ' minutes' }}
                            @elseif($package->order_delay_minutes === 0)
                                Immediately
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $package->created_at ? $package->created_at->format('Y-m-d H:i') : '' }}</td>
                        <td class="text-center">
                            <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <!-- <form action="{{ route('packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this package?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form> -->
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No packages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $packages->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
