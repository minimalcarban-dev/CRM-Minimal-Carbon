@php
    $pricingRows = $pricingRows ?? [];
    $pricingDefaults = $pricingDefaults ?? [];
    $canEditLabor = (bool) ($pricingDefaults['can_edit_labor'] ?? false);
    $canEditCommission = (bool) ($pricingDefaults['can_edit_commission'] ?? false);
    $canEditProfit = (bool) ($pricingDefaults['can_edit_profit'] ?? false);
    $canEditSalesMarkup = (bool) ($pricingDefaults['can_edit_sales_markup'] ?? false);
    $canViewProfit = (bool) ($pricingDefaults['can_view_profit'] ?? false);
    $laborDefault = (float) ($pricingDefaults['labor_rate_usd_per_gram'] ?? 20);
    $commissionDefault = (float) ($pricingDefaults['commission_percent'] ?? 20);
    $profitDefault = $canViewProfit || $canEditProfit ? (float) ($pricingDefaults['profit_percent'] ?? 25) : 0;
    $salesMarkupDefault = (float) ($pricingDefaults['sales_markup_percent'] ?? 0);
    $platinumDefault = (float) ($pricingDefaults['platinum_950_rate_usd_per_gram'] ?? 30);
    $isPlatinumLocked = env('JEWELLERY_PLATINUM_RATE') !== null && env('JEWELLERY_PLATINUM_RATE') !== '';
@endphp

<div class="form-section-card">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i
                    class="bi bi-calculator"></i></div>
            <div>
                <h3 class="section-title">Stock &amp; Pricing</h3>
                <p class="section-description">Quantity, live rates and per-variant pricing matrix</p>
            </div>
        </div>
    </div>
    <div class="section-body">

        <input type="hidden" name="purchase_price" id="purchase_price"
            value="{{ old('purchase_price', $jewelleryStock->purchase_price ?? 0) }}">
        <input type="hidden" name="selling_price" id="selling_price"
            value="{{ old('selling_price', $jewelleryStock->selling_price ?? 0) }}">


        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
            <div class="form-group">
                <label class="form-label">Initial Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" class="form-control"
                    value="{{ old('quantity', $jewelleryStock->quantity ?? 1) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Stock Threshold</label>
                <input type="number" name="low_stock_threshold" class="form-control"
                    value="{{ old('low_stock_threshold', $jewelleryStock->low_stock_threshold ?? 5) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Discount (%)</label>
                <input type="number" name="discount_percent" id="discount_percent_input" class="form-control"
                    step="0.01" min="0" max="100"
                    value="{{ old('discount_percent', $jewelleryStock->discount_percent ?? 0) }}">
            </div>
        </div>

        <div
            style="background:var(--light-gray,#f1f5f9);border:1px solid var(--border,#e2e8f0);border-radius:10px;padding:1rem;margin-bottom:1rem;">
            <div style="display: grid; grid-template-columns: repeat(5, minmax(120px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Labor / g ($)</label>
                    <input type="number" class="form-control pricing-assumption"
                        data-assumption="labor_rate_usd_per_gram" step="0.01" value="{{ $laborDefault }}"
                        {{ $canEditLabor ? '' : 'readonly' }}>
                </div>
                <div class="form-group">
                    <label class="form-label">950 Platinum / g ($)</label>
                    <div class="input-group">
                        <input type="number" name="platinum_950_rate_usd_per_gram" id="platinum_950_rate_usd_per_gram"
                            class="form-control" step="0.0001"
                            value="{{ old('platinum_950_rate_usd_per_gram', $pricingDefaults['platinum_950_rate_usd_per_gram'] ?? $platinumDefault) }}"
                            {{ ($isPlatinumLocked || !$canEditLabor) ? 'readonly' : '' }}>
                        @if($isPlatinumLocked)
                            <span class="input-group-text bg-light" title="Locked via .env config">
                                <i class="bi bi-lock-fill text-muted"></i>
                            </span>
                        @endif
                    </div>
                    @if($isPlatinumLocked)
                        <div class="form-text text-info" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle"></i> Managed via .env configuration
                        </div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">Commission %</label>
                    <input type="number" class="form-control pricing-assumption" data-assumption="commission_percent"
                        step="0.01" value="{{ $commissionDefault }}" {{ $canEditCommission ? '' : 'readonly' }}>
                </div>
                @if ($canViewProfit || $canEditProfit)
                    <div class="form-group">
                        <label class="form-label">Profit %</label>
                        <input type="number" class="form-control pricing-assumption" data-assumption="profit_percent"
                            step="0.01" value="{{ $profitDefault }}" {{ $canEditProfit ? '' : 'readonly' }}>
                    </div>
                @endif
                <div class="form-group">
                    <label class="form-label">Sales Markup %</label>
                    <input type="number" class="form-control pricing-assumption" data-assumption="sales_markup_percent"
                        step="0.01" value="{{ $salesMarkupDefault }}" {{ $canEditSalesMarkup ? '' : 'readonly' }}>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; margin-top: 1rem; flex-wrap: wrap;">
                <button type="button" class="btn-secondary-custom" onclick="copyPricingValue('stone_cost')">
                    <i class="bi bi-copy"></i> Copy Stone To All
                </button>
                <button type="button" class="btn-secondary-custom" onclick="copyPricingValue('extra_cost')">
                    <i class="bi bi-copy"></i> Copy Extra To All
                </button>
                <button type="button" class="btn-secondary-custom" onclick="fetchJewelleryPricingRates()">
                    <i class="bi bi-arrow-repeat"></i> Refresh Rate
                </button>
            </div>
        </div>

        <div id="pricingRateStatus"
            style="padding: 0.75rem; background: rgba(99, 102, 241, 0.08); border-radius: 8px; color: #4338ca; font-weight: 600; margin-bottom: 1rem;">
            Fetching live pricing rates...
        </div>

        <div style="overflow-x: auto;">
            <table class="table table-sm align-middle" style="min-width: 1320px;">
                <thead>
                    <tr>
                        <th>Default</th>
                        <th>Material</th>
                        <th>Yellow Wt(g)</th>
                        <th>White Wt(g)</th>
                        <th>Rose Wt(g)</th>
                        <th>Rate/g</th>
                        <th>Metal</th>
                        <th>Labor</th>
                        <th>Stone</th>
                        <th>Extra</th>
                        <th>Subtotal</th>
                        <th>Final Listing</th>
                        @if ($canViewProfit)
                            <th>Profit</th>
                        @endif
                        <th>Discounted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pricingRows as $key => $row)
                        @php
                            $oldBase = "pricing_variants.$key.";
                            $hiddenProfitPercent =
                                $canViewProfit || $canEditProfit
                                    ? old($oldBase . 'profit_percent', $row['profit_percent'] ?? $profitDefault)
                                    : 0;
                        @endphp
                        <tr class="pricing-row" data-key="{{ $key }}"
                            data-material="{{ $row['material_code'] }}"
                            data-is-gold="{{ str_starts_with($row['material_code'], 'gold_') ? 1 : 0 }}"
                            data-purity="{{ \App\Services\JewelleryPricingService::MATERIALS[$row['material_code']]['purity'] ?? 0 }}">
                            <td>
                                <input type="radio" name="default_pricing_variant" value="{{ $key }}"
                                    class="pricing-default-radio"
                                    {{ old('default_pricing_variant') ? (old('default_pricing_variant') === $key ? 'checked' : '') : ($row['is_default_listing'] ?? false ? 'checked' : '') }}>
                            </td>
                            <td style="font-weight: 700;">
                                {{ $row['material_label'] }}
                            </td>
                            @if (str_starts_with($row['material_code'], 'gold_'))
                                <td>
                                    <input type="number"
                                        name="pricing_variants[{{ $key }}][color_weights][yellow]"
                                        class="form-control pricing-input" data-field="color_weights.yellow"
                                        step="0.001"
                                        value="{{ old($oldBase . 'color_weights.yellow', $row['color_weights']['yellow'] ?? 0) }}">
                                </td>
                                <td>
                                    <input type="number"
                                        name="pricing_variants[{{ $key }}][color_weights][white]"
                                        class="form-control pricing-input" data-field="color_weights.white"
                                        step="0.001"
                                        value="{{ old($oldBase . 'color_weights.white', $row['color_weights']['white'] ?? 0) }}">
                                </td>
                                <td>
                                    <input type="number"
                                        name="pricing_variants[{{ $key }}][color_weights][rose]"
                                        class="form-control pricing-input" data-field="color_weights.rose"
                                        step="0.001"
                                        value="{{ old($oldBase . 'color_weights.rose', $row['color_weights']['rose'] ?? 0) }}">
                                </td>
                            @else
                                <td colspan="3">
                                    <input type="number"
                                        name="pricing_variants[{{ $key }}][net_weight_grams]"
                                        class="form-control pricing-input" data-field="net_weight_grams"
                                        step="0.001"
                                        value="{{ old($oldBase . 'net_weight_grams', $row['net_weight_grams'] ?? 0) }}">
                                </td>
                            @endif
                            <td class="pricing-rate">$0.00</td>
                            <td class="pricing-metal">$0.00</td>
                            <td class="pricing-labor">$0.00</td>
                            <td>
                                <input type="number" name="pricing_variants[{{ $key }}][stone_cost]"
                                    class="form-control pricing-input" data-field="stone_cost" step="0.01"
                                    value="{{ old($oldBase . 'stone_cost', $row['stone_cost'] ?? 0) }}">
                            </td>
                            <td>
                                <input type="number" name="pricing_variants[{{ $key }}][extra_cost]"
                                    class="form-control pricing-input" data-field="extra_cost" step="0.01"
                                    value="{{ old($oldBase . 'extra_cost', $row['extra_cost'] ?? 0) }}">
                            </td>
                            <td class="pricing-subtotal">$0.00</td>
                            <td class="pricing-listing" style="font-weight: 800; color: #6366f1;">$0.00</td>
                            @if ($canViewProfit)
                                <td class="pricing-profit">$0.00</td>
                            @endif
                            <td class="pricing-discounted" style="font-weight: 800; color: #10b981;">$0.00</td>
                            <input type="hidden"
                                name="pricing_variants[{{ $key }}][labor_rate_usd_per_gram]"
                                data-hidden-assumption="labor_rate_usd_per_gram" value="{{ $laborDefault }}">
                            <input type="hidden" name="pricing_variants[{{ $key }}][commission_percent]"
                                data-hidden-assumption="commission_percent"
                                value="{{ old($oldBase . 'commission_percent', $row['commission_percent'] ?? $commissionDefault) }}">
                            <input type="hidden" name="pricing_variants[{{ $key }}][profit_percent]"
                                data-hidden-assumption="profit_percent" value="{{ $hiddenProfitPercent }}">
                            <input type="hidden" name="pricing_variants[{{ $key }}][sales_markup_percent]"
                                data-hidden-assumption="sales_markup_percent"
                                value="{{ old($oldBase . 'sales_markup_percent', $row['sales_markup_percent'] ?? $salesMarkupDefault) }}">
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="margin_display"
            style="padding:0.75rem;background:rgba(16,185,129,0.1);border-radius:8px;font-weight:700;text-align:center;color:#10b981;margin-top:1rem;">
            Listing: $0.00
        </div>
    </div>{{-- /section-body --}}
</div>{{-- /form-section-card --}}

@push('scripts')
    <script>
        window.jewelleryPricingRatesUrl = @json(route('jewellery-stock.pricing-rates'));
        window.jewelleryPricingRates = window.jewelleryPricingRates || {
            gold_adjusted_usd_per_gram: 0,
            gold_adjusted_inr_per_gram: 0,
            silver_inr_per_gram: 0,
            silver_base_usd_per_gram: 0,
            platinum_950_usd_per_gram: {{ $platinumDefault }},
            source: 'settings'
        };

        function pricingNumber(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
        }

        function money(value) {
            return '$' + pricingNumber(value).toFixed(2);
        }

        function syncPricingAssumptions() {
            document.querySelectorAll('.pricing-assumption').forEach(input => {
                document.querySelectorAll(`[data-hidden-assumption="${input.dataset.assumption}"]`).forEach(
                    hidden => {
                        hidden.value = input.value || 0;
                    });
            });
        }

        function calculateJewelleryPricing() {
            syncPricingAssumptions();
            let selectedSubtotal = 0;
            let selectedListing = 0;
            const globalDiscount = pricingNumber(document.getElementById('discount_percent_input')?.value);

            document.querySelectorAll('.pricing-row').forEach(row => {
                const materialCode = row.dataset.material || '';
                const isSilver = materialCode.startsWith('silver_');
                const isGold = row.dataset.isGold === '1';
                const isPlatinum = materialCode === 'platinum_950';
                const purity = pricingNumber(row.dataset.purity);
                const yellowWeight = pricingNumber(row.querySelector('[data-field="color_weights.yellow"]')?.value);
                const whiteWeight = pricingNumber(row.querySelector('[data-field="color_weights.white"]')?.value);
                const roseWeight = pricingNumber(row.querySelector('[data-field="color_weights.rose"]')?.value);
                const manualWeight = pricingNumber(row.querySelector('[data-field="net_weight_grams"]')?.value);
                const weight = isGold ? Math.max(yellowWeight, whiteWeight, roseWeight) : manualWeight;
                const stone = pricingNumber(row.querySelector('[data-field="stone_cost"]')?.value);
                const extra = pricingNumber(row.querySelector('[data-field="extra_cost"]')?.value);
                const laborRate = pricingNumber(row.querySelector(
                    '[data-hidden-assumption="labor_rate_usd_per_gram"]')?.value);
                const commissionPct = pricingNumber(row.querySelector(
                    '[data-hidden-assumption="commission_percent"]')?.value);
                const profitPct = pricingNumber(row.querySelector('[data-hidden-assumption="profit_percent"]')
                    ?.value);
                const markupPct = pricingNumber(row.querySelector('[data-hidden-assumption="sales_markup_percent"]')
                    ?.value);
                const rate = isSilver ?
                    pricingNumber(window.jewelleryPricingRates.silver_base_usd_per_gram) * purity / 100 :
                    (isPlatinum ?
                        pricingNumber(document.getElementById('platinum_950_rate_usd_per_gram')?.value || window
                            .jewelleryPricingRates.platinum_950_usd_per_gram) :
                        pricingNumber(window.jewelleryPricingRates.gold_adjusted_usd_per_gram) * purity / 100);
                const metal = weight * rate;
                const labor = weight * laborRate;
                const subtotal = metal + labor + stone + extra;
                const commission = subtotal * commissionPct / 100;
                const afterCommission = subtotal + commission;
                const profit = afterCommission * profitPct / 100;
                const afterProfit = afterCommission + profit;
                const markup = afterProfit * markupPct / 100;
                const listing = afterProfit + markup;
                const discounted = listing * (1 - globalDiscount / 100);

                row.querySelector('.pricing-rate').innerText = money(rate);
                row.querySelector('.pricing-metal').innerText = money(metal);
                row.querySelector('.pricing-labor').innerText = money(labor);
                row.querySelector('.pricing-subtotal').innerText = money(subtotal);
                row.querySelector('.pricing-listing').innerText = money(listing);
                row.querySelector('.pricing-discounted').innerText = money(discounted);
                row.querySelector('.pricing-profit') && (row.querySelector('.pricing-profit').innerText = money(
                    profit));

                if (row.querySelector('.pricing-default-radio')?.checked) {
                    selectedSubtotal = subtotal;
                    selectedListing = listing;
                }
            });

            document.getElementById('purchase_price').value = selectedSubtotal.toFixed(2);
            document.getElementById('selling_price').value = selectedListing.toFixed(2);
            const display = document.getElementById('margin_display');
            if (display) {
                const discLabel = globalDiscount > 0 ? ` | Discounted: ${money(selectedListing * (1 - globalDiscount / 100))}` : '';
                display.innerText = `Listing: ${money(selectedListing)}${discLabel} | Cost: ${money(selectedSubtotal)}`;
            }
        }

        function copyPricingValue(field) {
            const inputs = document.querySelectorAll(`[data-field="${field}"]`);
            const source = inputs[0]?.value || 0;
            inputs.forEach(input => input.value = source);
            calculateJewelleryPricing();
        }

        async function fetchJewelleryPricingRates() {
            const status = document.getElementById('pricingRateStatus');
            try {
                status.innerText = 'Fetching live gold and silver rates...';
                const response = await fetch(window.jewelleryPricingRatesUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                if (payload.success) {
                    window.jewelleryPricingRates = payload.rates;
                    const platinumInput = document.getElementById('platinum_950_rate_usd_per_gram');
                    if (payload.rates.is_platinum_locked && platinumInput) {
                        platinumInput.value = pricingNumber(payload.rates.platinum_950_usd_per_gram).toFixed(4);
                    }
                    const sourceText = payload.rates.source || 'live';
                    const lockIcon = payload.rates.is_platinum_locked ? '<i class="bi bi-lock-fill" title="Locked via config"></i> ' : '';
                    
                    status.innerHTML = 
                        `Gold 24K adj: INR ${pricingNumber(payload.rates.gold_adjusted_inr_per_gram).toFixed(2)}/g (${money(payload.rates.gold_adjusted_usd_per_gram)}/g) | ` +
                        `Silver base: INR ${pricingNumber(payload.rates.silver_inr_per_gram).toFixed(2)}/g (${money(payload.rates.silver_base_usd_per_gram)}/g) | ` +
                        `Platinum 950: ${lockIcon}${money(payload.rates.platinum_950_usd_per_gram)}/g | ` +
                        `Source: ${sourceText}`;
                } else {
                    status.innerText = 'Live rate unavailable. Server will recalculate on save.';
                }
            } catch (error) {
                status.innerText = 'Live rate unavailable. Server will recalculate on save.';
            }
            calculateJewelleryPricing();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.pricing-input, .pricing-assumption, .pricing-default-radio').forEach(el => {
                el.addEventListener('input', calculateJewelleryPricing);
                el.addEventListener('change', calculateJewelleryPricing);
            });
            document.getElementById('discount_percent_input')?.addEventListener('input', calculateJewelleryPricing);
            document.getElementById('platinum_950_rate_usd_per_gram')?.addEventListener('input',
                calculateJewelleryPricing);
            fetchJewelleryPricingRates();
        });
    </script>
@endpush
