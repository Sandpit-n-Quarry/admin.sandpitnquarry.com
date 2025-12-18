@extends('layout.layout')
@php
    $title='Coin Promotions';
    $subTitle = 'Coin Promotions List';
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
                        </form>
                        <form class="navbar-search" method="GET">
                            <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search" value="{{ request('search') }}">
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                        </form>
                    </div>
                    <a href="{{ route('coin-promotions.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Add New Coin Promotion
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
                                    <th scope="col">Start Date</th>
                                    <th scope="col">Creator</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coinPromotions as $index => $promotion)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-10">
                                                    <div class="form-check style-check d-flex align-items-center">
                                                        <input class="form-check-input radius-4 border border-neutral-400" type="checkbox" name="checkbox" value="{{ $promotion->id }}">
                                                    </div>
                                                    <span class="copy-text" data-clipboard-text="{{ $promotion->id }}">
                                                        {{ $promotion->id }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td><span class="text-md mb-0 fw-normal text-secondary-light">{{ $promotion->start_at ? $promotion->start_at->format('Y-m-d H:i:s') : 'N/A' }}</span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if(optional($promotion->creator)->profile_photo_path)
                                                        <img src="{{ asset('storage/' . $promotion->creator->profile_photo_path) }}" alt="{{ $promotion->creator->name }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                    @else
                                                        <img src="{{ asset('assets/images/user.png') }}" alt="{{ optional($promotion->creator)->name ?? 'N/A' }}" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                    @endif
                                                    <div class="d-flex flex-column flex-grow-1 min-w-0">
                                                        <span class="fw-medium text-sm text-truncate">{{ optional($promotion->creator)->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="text-sm text-secondary-light">{{ $promotion->created_at->format('Y-m-d H:i:s') }}</span></td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="{{ route('coin-promotions.edit', $promotion->id) }}" class="btn btn-sm btn-warning text-white radius-4">
                                                        <iconify-icon icon="uil:edit" class="text-lg"></iconify-icon>
                                                    </a>
                                                    <!-- <form action="{{ route('coin-promotions.destroy', $promotion->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger radius-4" onclick="return confirm('Are you sure you want to delete this promotion?')">
                                                            <iconify-icon icon="uil:trash-alt" class="text-lg"></iconify-icon>
                                                        </button>
                                                    </form> -->
                                                </div>
                                            </td>
                                        </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No coin promotions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-20">
                        <div class="text-secondary-light">
                            Showing {{ $coinPromotions->firstItem() ?? 0 }} to {{ $coinPromotions->lastItem() ?? 0 }} of {{ $coinPromotions->total() ?? 0 }} entries
                        </div>
                        {{ $coinPromotions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            
            <script>
                // Copy text functionality
                document.addEventListener('DOMContentLoaded', function() {
                    new ClipboardJS('.copy-text').on('success', function(e) {
                        // Create tooltip
                        const tooltip = document.createElement('div');
                        tooltip.classList.add('copy-tooltip');
                        tooltip.textContent = 'ID copied!';
                        document.body.appendChild(tooltip);
                        
                        // Position tooltip
                        const rect = e.trigger.getBoundingClientRect();
                        tooltip.style.top = `${rect.top - 30}px`;
                        tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
                        tooltip.classList.add('show');
                        
                        // Remove tooltip after animation
                        setTimeout(() => {
                            tooltip.remove();
                        }, 1500);
                        
                        e.clearSelection();
                    });
                    
                    // Select all checkbox
                    document.getElementById('selectAll').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('tbody .form-check-input');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                });
            </script>
            
            <style>
                .copy-text {
                    cursor: pointer;
                }
                .copy-tooltip {
                    position: fixed;
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 5px;
                    font-size: 12px;
                    pointer-events: none;
                    opacity: 0;
                    transition: opacity 0.3s;
                    z-index: 9999;
                }
                .copy-tooltip.show {
                    opacity: 1;
                }
            </style>
@endsection