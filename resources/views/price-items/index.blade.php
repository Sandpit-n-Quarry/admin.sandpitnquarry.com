@extends('layout.layout')
@php
use App\Models\Price;

$title = 'Prices';
$subTitle = 'Price Management';

$perPage = (int) request('per_page', 10);
$search = request('search');
$query = Price::query();
if ($search) {
    $query->where('name', 'like', "%{$search}%");
}
$prices = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
@endphp

@section('content')
<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <span class="text-md fw-medium text-secondary-light mb-0">Show</span>
            <form method="GET" id="per-page-form">
                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px" name="per_page" onchange="document.getElementById('per-page-form').submit()">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
                @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
            </form>

            <form class="navbar-search" method="GET" id="search-form">
                <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search prices..." value="{{ request('search') }}">
                <iconify-icon icon="ion:search-outline" class="icon" onclick="document.getElementById('search-form').submit()"></iconify-icon>
                @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <button type="submit" class="d-none"></button>
            </form>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('prices.create') }}" class="btn btn-sm btn-primary">New price</a>
        </div>
    </div>

    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Published at</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $price)
                    <tr>
                        <td>{{ $price->id }}</td>
                        <td>{{ $price->name }}</td>
                        <td>{{ optional($price->published_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ optional($price->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ optional($price->updated_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('prices.edit', $price->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <a href="{{ route('prices.tonne', $price->id) }}" class="btn btn-sm btn-link">Tonne</a>
                                <a href="{{ route('prices.load', $price->id) }}" class="btn btn-sm btn-link">Load</a>
                                <!-- <form action="{{ route('prices.destroy', $price->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirmDeletePrice(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form> -->
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No prices found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                @if($prices->total() > 0)
                Showing {{ $prices->firstItem() }} to {{ $prices->lastItem() }} of {{ $prices->total() }} entries
                @else
                Showing 0 to 0 of 0 entries
                @endif
            </span>

            <div>
                {{ $prices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDeletePrice(event) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this price?')) {
            event.target.submit();
        }
        return false;
    }
    $(document).ready(function() {
        // placeholder for future JS
    });
</script>
@endpush
