@extends('layouts.admin')
@section('title', 'Jewellery Price Calculator')

@section('content')
    <div class="tracker-page">
        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link"><i class="bi bi-house-door"></i>
                            Dashboard</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Jewellery Price Calculator</span>
                    </div>
                    <h1 class="page-title"><i class="bi bi-calculator" style="color:#8b5cf6;"></i> Jewellery Price Calculator</h1>
                    <p class="page-subtitle">Standalone tool to calculate jewellery pricing variants based on gemstone and metal details.</p>
                </div>
                <div class="header-right">
                    <button type="button" class="btn-secondary-custom" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Reset Calculator
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Gemstone Details ── --}}
        <div class="form-section-card" style="margin-bottom:1.5rem;">
            <div class="section-header">
                <div class="section-info">
                    <div class="section-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);"><i
                            class="bi bi-diamond-half"></i></div>
                    <div>
                        <h3 class="section-title">Gemstone Details</h3>
                        <p class="section-description">Primary and side stone specifications</p>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div
                    style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 2px solid rgba(139, 92, 246, 0.1); padding-bottom: 1rem;">
                    <div>
                        <h4
                            style="margin: 0; font-size: 0.95rem; color: #6d28d9; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                            Total Carat Weight & Stone Cost</h4>
                        <p style="margin: 0; font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem;">Combined weight
                            & price of primary and all secondary stones</p>
                    </div>
                    <div style="display: flex; gap: 2rem; align-items: center;">
                        <div style="text-align: center;">
                            <div
                                style="font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Weight</div>
                            <span id="total_carat_weight_display"
                                style="font-size: 1.5rem; font-weight: 800; color: #6d28d9;">0.000 cts</span>
                        </div>
                        <div style="width: 1px; height: 2rem; background: rgba(139, 92, 246, 0.2);"></div>
                        <div style="text-align: center;">
                            <div
                                style="font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Price</div>
                            <span id="total_stone_price_display"
                                style="font-size: 1.5rem; font-weight: 800; color: #10b981;">$0.00</span>
                        </div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
                    {{-- Primary Stone --}}
                    <div>
                        <p
                            style="font-size:0.8rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem;">
                            <i class="bi bi-circle-fill"
                                style="font-size:.5rem;vertical-align:middle;margin-right:.4rem;"></i>Primary Stone
                        </p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Stone Type</label>
                                <select name="primary_stone_type_id" class="form-control themed-select">
                                    <option value="">Select Stone</option>
                                    @foreach ($stoneTypes as $stone)
                                        <option value="{{ $stone->id }}">{{ $stone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Carat Weight</label>
                                <input type="number" name="primary_stone_weight" class="form-control"
                                    step="0.001" placeholder="0.000 cts">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Price/Ct ($)</label>
                                <input type="number" name="primary_stone_price" class="form-control"
                                    step="0.01" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Shape</label>
                                <select name="primary_stone_shape_id" class="form-control themed-select">
                                    <option value="">Select Shape</option>
                                    @foreach ($stoneShapes as $shape)
                                        <option value="{{ $shape->id }}">{{ $shape->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Measurement</label>
                                <input type="text" name="primary_stone_measurement" class="form-control"
                                    placeholder="e.g. 5x3 mm">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cut Grade</label>
                                <select name="primary_stone_cut_id" class="form-control themed-select">
                                    <option value="">Select Cut</option>
                                    @foreach ($diamondCuts as $cut)
                                        <option value="{{ $cut->id }}">{{ $cut->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Color</label>
                                <select name="primary_stone_color_id" class="form-control themed-select">
                                    <option value="">Select Color</option>
                                    @foreach ($stoneColors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Clarity</label>
                                <select name="primary_stone_clarity_id" class="form-control themed-select">
                                    <option value="">Select Clarity</option>
                                    @foreach ($diamondClarities as $clarity)
                                        <option value="{{ $clarity->id }}">{{ $clarity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Primary Stone Subtotal --}}
                        <div
                            style="margin-top: 1rem; padding: 0.5rem 0; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(99, 102, 241, 0.1);">
                            <span
                                style="font-size: 0.8rem; font-weight: 700; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.04em;"><i
                                    class="bi bi-gem" style="margin-right: 0.3rem;"></i>Primary Stone Total</span>
                            <div style="display: flex; gap: 1.5rem; align-items: center;">
                                <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 600;">Wt: <span
                                        id="primary_stone_total_weight">0.000</span> cts</span>
                                <span style="font-size: 0.85rem; color: #10b981; font-weight: 600;">Price: $<span
                                        id="primary_stone_total_price">0.00</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Side Stones Repeater --}}
                    <div>
                        @include('jewellery-stock.partials.side-stones-repeater', ['jewelleryStock' => null])
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stock & Pricing (Matrix) ── --}}
        @include('jewellery-stock.partials.pricing-matrix', ['jewelleryStock' => null])

    </div>
@endsection

@push('scripts')
    <script>
        function calculateTotalCaratWeight() {
            let totalWeight = 0;
            let totalPrice = 0;

            // Primary stone weight & price
            const primaryWeightInput = document.querySelector('input[name="primary_stone_weight"]');
            const primaryPriceInput = document.querySelector('input[name="primary_stone_price"]');
            let pW = 0, pP = 0, pTotal = 0;
            
            if (primaryWeightInput && primaryWeightInput.value) {
                pW = parseFloat(primaryWeightInput.value) || 0;
            }
            if (primaryPriceInput && primaryPriceInput.value) {
                pP = parseFloat(primaryPriceInput.value) || 0;
            }
            
            if (pW > 0) {
                totalWeight += pW;
                pTotal = pW * pP;
                totalPrice += pTotal;
            }

            // Primary stone subtotal display
            const pWtEl = document.getElementById('primary_stone_total_weight');
            const pPrEl = document.getElementById('primary_stone_total_price');
            if (pWtEl) pWtEl.innerText = pW.toFixed(3);
            if (pPrEl) pPrEl.innerText = pTotal.toFixed(2);

            // Side stones
            let sideWeight = 0, sidePriceTotal = 0;
            const sideStoneWeights = document.querySelectorAll('input[name^="side_stones"][name$="[weight]"]');
            sideStoneWeights.forEach(input => {
                const name = input.name;
                const priceName = name.replace('[weight]', '[price]');
                const pInput = document.querySelector(`input[name="${priceName}"]`);
                
                let w = 0, p = 0, rowTotal = 0;
                if (input.value) w = parseFloat(input.value) || 0;
                if (pInput && pInput.value) p = parseFloat(pInput.value) || 0;
                
                if (w > 0) {
                    totalWeight += w;
                    sideWeight += w;
                    rowTotal = w * p;
                    sidePriceTotal += rowTotal;
                    totalPrice += rowTotal;
                }
            });

            // Side stones subtotal display
            const sWtEl = document.getElementById('side_stones_total_weight');
            const sPrEl = document.getElementById('side_stones_total_price');
            if (sWtEl) sWtEl.innerText = sideWeight.toFixed(3);
            if (sPrEl) sPrEl.innerText = sidePriceTotal.toFixed(2);

            // Grand total display
            const weightDisplay = document.getElementById('total_carat_weight_display');
            if (weightDisplay) {
                weightDisplay.innerText = totalWeight.toFixed(3) + ' cts';
            }
            const priceDisplay = document.getElementById('total_stone_price_display');
            if (priceDisplay) {
                priceDisplay.innerText = '$' + totalPrice.toFixed(2);
            }

            // Auto-fill stone_cost in ALL pricing matrix rows
            const stoneCostInputs = document.querySelectorAll('.pricing-row [data-field="stone_cost"]');
            stoneCostInputs.forEach(input => {
                input.value = totalPrice.toFixed(2);
            });

            // Recalculate pricing matrix
            if (typeof calculateJewelleryPricing === 'function') {
                calculateJewelleryPricing();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.body.addEventListener('input', (e) => {
                if (e.target.name === 'primary_stone_weight' ||
                    e.target.name === 'primary_stone_price' ||
                    (e.target.name && e.target.name.startsWith('side_stones') &&
                        (e.target.name.endsWith('[weight]') || e.target.name.endsWith('[price]')))) {
                    calculateTotalCaratWeight();
                }
            });
            document.body.addEventListener('click', (e) => {
                if (e.target.closest('button[onclick^="removeSideStoneRow"]')) {
                    setTimeout(calculateTotalCaratWeight, 50);
                }
            });
            calculateTotalCaratWeight();
        });
    </script>
@endpush
