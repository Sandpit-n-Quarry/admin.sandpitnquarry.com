@extends('layout.layout')
@php
    $title='Edit Coin Promotion';
    $subTitle = 'Edit coin promotion';
@endphp

@section('content')
    <div class="card h-100 p-0 radius-12 mb-24">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h5 class="mb-0 text-lg fw-medium">Edit Coin Promotion</h5>
        </div>
        <div class="card-body p-24">
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('coin-promotions.update', $coinPromotion->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-20">
                    <div class="col-md-6">
                        <label for="start_at" class="form-label text-secondary-light mb-10">Start Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" id="start_at" name="start_at" value="{{ old('start_at', $coinPromotion->start_at ? $coinPromotion->start_at->format('Y-m-d\TH:i') : '') }}">
                        @error('start_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3 mt-30">
                    <a href="{{ route('coin-promotions.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Promotion</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Promotion Details Section -->
    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-lg fw-medium">Promotion Details</h5>
            <a href="{{ route('coin-promotion-details.create', ['coin_promotion_id' => $coinPromotion->id]) }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                Add New Detail
            </a>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Price</th>
                            <th scope="col">Coins</th>
                            <th scope="col">Free Coins</th>
                            <th scope="col">Discount (%)</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coinPromotion->coin_promotion_details as $detail)
                            <tr>
                                <td>{{ $detail->id }}</td>
                                <td>{{ number_format($detail->price / 100, 2) }}</td>
                                <td>{{ number_format($detail->coins) }}</td>
                                <td>{{ number_format($detail->free_coins) }}</td>
                                <td>{{ $detail->discount }}%</td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('coin-promotion-details.edit', $detail->id) }}" class="btn btn-sm btn-warning text-white radius-4">
                                            <iconify-icon icon="uil:edit" class="text-lg"></iconify-icon>
                                        </a>
                                        <!-- <form action="{{ route('coin-promotion-details.destroy', $detail->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger radius-4" onclick="return confirm('Are you sure you want to delete this detail?')">
                                                <iconify-icon icon="uil:trash-alt" class="text-lg"></iconify-icon>
                                            </button>
                                        </form> -->
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No promotion details found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection