@extends('layout.layout')
@php
$title='Load Prices';
$subTitle = 'Price Item Management';
@endphp

@section('content')
<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <h4 class="mb-0">Load Prices (ID: {{ $price->id }})</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('prices') }}" class="btn btn-sm btn-outline-primary">Back to Price List</a>
        </div>
    </div>

    <div class="card-body p-24">
        <div class="row mb-3">
            <div class="col-md-6">
                <form class="navbar-search" method="GET">
                    <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search zones..." value="{{ request('search') }}">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    <input type="hidden" name="price_id" value="{{ $price->id }}">
                    <button type="submit" class="d-none"></button>
                </form>
            </div>
        </div>

        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Zone</th>
                        <th scope="col">State</th>
                        @foreach($wheels as $wheel)
                        @foreach($products as $product)
                        <th scope="col">{{ $product->name }}({{ $wheel }})</th>
                        @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($zonesData as $zone)
                    <tr>
                        <td>{{ $zone['id'] }}</td>
                        <td>{{ $zone['name'] }}</td>
                        <td>{{ $zone['state'] }}</td>
                        @foreach($wheels as $wheel)
                        @foreach($products as $product)
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <input
                                    type="number"
                                    class="form-control form-control-sm load-price-input"
                                    data-price-id="{{ $price->id }}"
                                    data-zone-id="{{ $zone['id'] }}"
                                    data-product-id="{{ $product->id }}"
                                    data-wheel-id="{{ $wheel }}"
                                    value="{{ ($zone['wheels'][$wheel]['products'][$product->id]['amount'] ?? 0) / 100 }}"
                                    step="0.01"
                                    min="0">
                                <button type="button" class="btn btn-sm btn-outline-primary price-save-btn" title="Save">Save</button>
                            </div>
                        </td>
                        @endforeach
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>
                Showing {{ $zones->firstItem() ?? 0 }} to {{ $zones->lastItem() ?? 0 }} of {{ $zones->total() ?? 0 }} entries
            </span>

            {{-- Custom Pagination --}}
            @if ($zones->hasPages())
            <nav aria-label="Zone pagination">
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    {{-- Previous Page Link --}}
                    @if ($zones->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                            <iconify-icon icon="material-symbols:arrow-back-ios-rounded"></iconify-icon>
                        </span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="{{ $zones->previousPageUrl() }}{{ request()->getQueryString() ? '&'.request()->getQueryString() : '' }}">
                            <iconify-icon icon="material-symbols:arrow-back-ios-rounded"></iconify-icon>
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements with Ellipsis --}}
                    @php
                    $total = $zones->lastPage();
                    $current = $zones->currentPage();
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
                            @elseif ($page == $zones->currentPage())
                            <li class="page-item active">
                                <span class="page-link text-white fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600">{{ $page }}</span>
                            </li>
                            @else
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $zones->url($page) }}{{ request()->getQueryString() ? '&'.request()->getQueryString() : '' }}">{{ $page }}</a>
                            </li>
                            @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($zones->hasMorePages())
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                                    href="{{ $zones->nextPageUrl() }}{{ request()->getQueryString() ? '&'.request()->getQueryString() : '' }}">
                                    <iconify-icon icon="material-symbols:arrow-forward-ios-rounded"></iconify-icon>
                                </a>
                            </li>
                            @else
                            <li class="page-item disabled">
                                <span class="page-link bg-neutral-200 text-neutral-400 fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md">
                                    <iconify-icon icon="material-symbols:arrow-forward-ios-rounded"></iconify-icon>
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
        // Small visual styles for save state
        const styleId = 'price-input-states-style';
        if (!document.getElementById(styleId)) {
            const s = document.createElement('style');
            s.id = styleId;
            s.innerHTML = `
                .is-saving { outline: 2px dashed #f59e0b !important; box-shadow: 0 0 6px rgba(245,158,11,0.25) !important; }
                /* Hover shows green while saving to indicate active save */
                .is-saving:hover { outline: 2px solid #10b981 !important; box-shadow: 0 0 8px rgba(16,185,129,0.25) !important; }
                .is-saved { outline: 2px solid #10b981 !important; box-shadow: 0 0 6px rgba(16,185,129,0.15) !important; }
                .is-error { outline: 2px solid #ef4444 !important; box-shadow: 0 0 6px rgba(239,68,68,0.15) !important; }
            `;
            document.head.appendChild(s);
        }

        // Update matching inputs when a canonical price update occurs
        window.addEventListener('price.updated', function(e) {
            try {
                const data = e.detail;
                const priceId = data.price_id;
                const productId = data.product_id;
                const wheelId = data.wheel_id;
                const itemableType = data.price_itemable_type;
                const itemableId = data.price_itemable_id;

                const selectorParts = [];
                selectorParts.push('[data-price-id="'+priceId+'"]');
                selectorParts.push('[data-product-id="'+productId+'"]');
                if (wheelId !== null && typeof wheelId !== 'undefined') {
                    selectorParts.push('[data-wheel-id="'+wheelId+'"]');
                }
                if (itemableType === 'site') {
                    selectorParts.push('[data-site-id="'+itemableId+'"]');
                } else if (itemableType === 'zone') {
                    selectorParts.push('[data-zone-id="'+itemableId+'"]');
                }

                const selector = selectorParts.join('');
                const inputs = document.querySelectorAll(selector);
                inputs.forEach(function(input) {
                    if (typeof data.amount_display !== 'undefined') {
                        input.value = data.amount_display;
                        input.classList.remove('is-saving', 'is-error');
                        input.classList.add('is-saved');
                        setTimeout(function() { input.classList.remove('is-saved'); }, 1200);
                        if (window.jQuery && window.jQuery(input).data) {
                            window.jQuery(input).data('lastSaved', String(data.amount_display));
                        }
                    }
                });
            } catch (err) {
                console.debug('price.updated handler error', err);
            }
        });

        // Shared immediate auto-save for inputs (no debounce) — same behavior as tonne view
        function attachAutoSave(selector, url, buildPayload) {
            $(selector).each(function() {
                const $input = $(this);
                $input.data('lastSaved', $input.val());

                function doSave() {
                    const current = $input.val();
                    if ($input.data('lastSaved') === current) return;

                    $input.removeClass('is-saved is-error').addClass('is-saving');

                    const payload = buildPayload($input);
                    payload._token = '{{ csrf_token() }}';

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: payload,
                        dataType: 'json',
                        headers: { 'Accept': 'application/json' },
                        success: function(response) {
                            console.debug('save success', response);
                            if (response && response.success) {
                                // After POST returns, fetch canonical item from server
                                const query = {
                                    price_id: payload.price_id,
                                    product_id: payload.product_id,
                                    wheel_id: payload.wheel_id,
                                    zone_id: payload.zone_id
                                };

                                $.ajax({
                                    url: '{{ route("prices.item.get") }}',
                                    method: 'GET',
                                    data: query,
                                    dataType: 'json',
                                    headers: { 'Accept': 'application/json' },
                                    success: function(getResp) {
                                        if (getResp && getResp.success) {
                                            if (typeof getResp.amount_display !== 'undefined') {
                                                $input.val(getResp.amount_display);
                                            }
                                            $input.data('lastSaved', $input.val());
                                            $input.removeClass('is-saving is-error').addClass('is-saved');
                                            setTimeout(function() { $input.removeClass('is-saved'); }, 1200);
                                            // dispatch a custom event so other parts of the UI can react (Livewire-like)
                                            const evt = new CustomEvent('price.updated', { detail: getResp });
                                            window.dispatchEvent(evt);
                                        } else {
                                            $input.removeClass('is-saving is-saved').addClass('is-error');
                                            toastr.error(getResp.message || 'Failed to fetch saved price');
                                        }
                                    },
                                    error: function(xhr) {
                                        console.debug('get canonical error', xhr);
                                        $input.removeClass('is-saving is-saved').addClass('is-error');
                                    }
                                });

                                if (response.deleted) {
                                    $input.val('');
                                }
                            } else {
                                $input.removeClass('is-saving is-saved').addClass('is-error');
                                toastr.error(response.message || 'Failed to update price');
                            }
                        },
                        error: function(xhr) {
                            console.debug('save error', xhr);
                            $input.removeClass('is-saving is-saved').addClass('is-error');
                            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message);
                            } else if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                                const keys = Object.keys(xhr.responseJSON.errors);
                                if (keys.length) toastr.error(xhr.responseJSON.errors[keys[0]][0]);
                            } else {
                                toastr.error('Failed to update price');
                            }
                        }
                    });
                }

                // Remove immediate auto-save handlers; saving is delegated to Save button only
                // Inputs will be saved when their associated .price-save-btn is clicked.
            });
        }

        attachAutoSave('.load-price-input', '{{ route("prices.load.update") }}', function($input) {
            return {
                price_id: $input.data('price-id'),
                zone_id: $input.data('zone-id'),
                product_id: $input.data('product-id'),
                wheel_id: $input.data('wheel-id'),
                amount: $input.val()
            };
        });

        // Delegate Save button clicks to trigger a save for the associated input
        $(document).on('click', '.price-save-btn', function() {
            const $btn = $(this);
            const $input = $btn.closest('div').find('input[type="number"]');
            if ($input && $input.length) {
                // Build payload and run the same save routine used in attachAutoSave
                const payload = {
                    price_id: $input.data('price-id'),
                    zone_id: $input.data('zone-id'),
                    product_id: $input.data('product-id'),
                    wheel_id: $input.data('wheel-id'),
                    amount: $input.val(),
                    _token: '{{ csrf_token() }}'
                };

                // Visual feedback
                $input.removeClass('is-saved is-error').addClass('is-saving');

                $.ajax({
                    url: '{{ route("prices.load.update") }}',
                    method: 'POST',
                    data: payload,
                    dataType: 'json',
                    headers: { 'Accept': 'application/json' },
                    success: function(response) {
                        if (response && response.success) {
                            const query = {
                                price_id: payload.price_id,
                                product_id: payload.product_id,
                                wheel_id: payload.wheel_id,
                                zone_id: payload.zone_id
                            };

                            $.ajax({
                                url: '{{ route("prices.item.get") }}',
                                method: 'GET',
                                data: query,
                                dataType: 'json',
                                headers: { 'Accept': 'application/json' },
                                success: function(getResp) {
                                    if (getResp && getResp.success) {
                                        if (typeof getResp.amount_display !== 'undefined') {
                                            $input.val(getResp.amount_display);
                                        }
                                        $input.data('lastSaved', $input.val());
                                        $input.removeClass('is-saving is-error').addClass('is-saved');
                                        setTimeout(function() { $input.removeClass('is-saved'); }, 1200);
                                        const evt = new CustomEvent('price.updated', { detail: getResp });
                                        window.dispatchEvent(evt);
                                    } else {
                                        $input.removeClass('is-saving is-saved').addClass('is-error');
                                        toastr.error(getResp.message || 'Failed to fetch saved price');
                                    }
                                },
                                error: function(xhr) {
                                    $input.removeClass('is-saving is-saved').addClass('is-error');
                                }
                            });

                            if (response.deleted) {
                                $input.val('');
                            }
                        } else {
                            $input.removeClass('is-saving is-saved').addClass('is-error');
                            toastr.error(response.message || 'Failed to update price');
                        }
                    },
                    error: function(xhr) {
                        $input.removeClass('is-saving is-saved').addClass('is-error');
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                            const keys = Object.keys(xhr.responseJSON.errors);
                            if (keys.length) toastr.error(xhr.responseJSON.errors[keys[0]][0]);
                        } else {
                            toastr.error('Failed to update price');
                        }
                    }
                });
            }
        });

        // Enter key fallback: prevent form submission but do NOT auto-save. Saving must be done via Save button.
        $(document).on('keypress', '.tonne-price-input, .load-price-input', function(e) {
            if (e.key === 'Enter' || e.which === 13) {
                e.preventDefault();
                // do nothing else — user should click Save to persist changes
            }
        });
    });
</script>
@endpush