{{-- Multi Melee Diamond Selector - View identical to Diamond SKU Selector --}}
<style>
    /* ===== Melee Pill Container (matches .sku-pills-container) ===== */
    .melee-pills-container {
        display: none;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .melee-pills-container.has-pills {
        display: flex;
    }

    /* ===== Individual Pill (matches .sku-pill) ===== */
    .melee-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 50px;
        font-size: 0.8125rem;
        transition: all 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        animation: meleePillFadeIn 0.3s ease;
    }
    .melee-pill:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.12);
        transform: translateY(-1px);
    }

    /* Name badge (matches .sku-pill-sku) */
    .melee-pill-name {
        font-weight: 700;
        font-size: 0.75rem;
        color: #4f46e5;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        letter-spacing: 0.3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }

    /* Details text (matches .sku-pill-details) */
    .melee-pill-details {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
    }

    /* Pieces input per pill (matches .diamond-price-wrapper) */
    .melee-pill-pieces-wrapper {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.15rem 0.35rem;
        transition: all 0.2s ease;
    }
    .melee-pill-pieces-wrapper:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
    .melee-pill-pieces-wrapper label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        margin: 0;
        white-space: nowrap;
    }
    .melee-pill-pieces-input {
        width: 40px;
        padding: 0.2rem 0.25rem;
        border: none;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 600;
        color: #1f2937;
        text-align: right;
        outline: none;
        -moz-appearance: textfield;
    }
    .melee-pill-pieces-input::-webkit-outer-spin-button,
    .melee-pill-pieces-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Price display per pill */
    .melee-pill-price {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        padding: 2px 8px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 6px;
        font-size: 0.6875rem;
        color: #047857;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Remove button (matches .sku-pill-remove) */
    .melee-pill-remove {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        padding: 0;
        margin-left: 0.25rem;
        flex-shrink: 0;
    }
    .melee-pill-remove:hover {
        background: #fee2e2;
        color: #ef4444;
        transform: scale(1.1);
    }

    /* ===== Aggregate Row ===== */
    .melee-aggregate-row {
        display: flex;
        gap: 16px;
        margin-top: 0.75rem;
        flex-wrap: wrap;
    }
    .melee-aggregate-item {
        flex: 1;
        min-width: 140px;
    }
    .melee-aggregate-item label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .melee-aggregate-item input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.8125rem;
        background: #f3f4f6;
        color: #374151;
    }

    @keyframes meleePillFadeIn {
        from { opacity: 0; transform: scale(0.9) translateY(-4px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<div class="form-group-modern">
    <label class="form-label-modern">
        <span class="label-content">
            <span class="label-icon"><i class="bi bi-gem"></i></span>
            <span class="label-text">Side Stones / Melee</span>
        </span>
        <span class="optional-badge">Optional</span>
    </label>

    {{-- Compute melee entries JSON with backward compat for old orders --}}
    @php
        $meleeEntriesJson = '[]';
        if (isset($order)) {
            if (!empty($order->melee_entries)) {
                $entries = $order->melee_entries;
                // Enrich entries that may be missing 'name' (saved before name was stored)
                $needsEnrichment = collect($entries)->contains(fn($e) => empty($e['name']));
                if ($needsEnrichment) {
                    $meleeIds = collect($entries)->pluck('melee_diamond_id')->filter()->all();
                    $melees = \App\Models\MeleeDiamond::with('category')->whereIn('id', $meleeIds)->get()->keyBy('id');
                    $entries = array_map(function($e) use ($melees) {
                        if (empty($e['name']) && isset($melees[$e['melee_diamond_id']])) {
                            $d = $melees[$e['melee_diamond_id']];
                            $typeLabel = optional($d->category)->type === 'lab_grown' ? 'Lab Grown' : 'Natural';
                            $e['name'] = "[{$typeLabel}] " . optional($d->category)->name . " - {$d->shape} - {$d->size_label}";
                        }
                        return $e;
                    }, $entries);
                }
                $meleeEntriesJson = json_encode(array_values($entries));
            } elseif ($order->melee_diamond_id) {
                // Backward compat: build entry from old single-melee columns
                $md = $order->meleeDiamond;
                $name = 'Melee #' . $order->melee_diamond_id;
                if ($md && $md->category) {
                    $typeLabel = $md->category->type === 'lab_grown' ? 'Lab Grown' : 'Natural';
                    $name = "[{$typeLabel}] {$md->category->name} - {$md->shape} - {$md->size_label}";
                }
                $pieces = (int)($order->melee_pieces ?? 0);
                $meleeEntriesJson = json_encode([[
                    'melee_diamond_id' => $order->melee_diamond_id,
                    'name' => $name,
                    'pieces' => $pieces,
                    'avg_carat_per_piece' => ($order->melee_carat && $pieces > 0)
                        ? round($order->melee_carat / $pieces, 5) : 0,
                    'price_per_ct' => (float)($order->melee_price_per_ct ?? 0),
                    'available_pieces' => $md ? (int)$md->available_pieces : 0,
                ]]);
            }
        }
    @endphp

    {{-- Hidden input storing JSON array of all melee entries --}}
    <input type="hidden" name="melee_entries_json" id="melee_entries_json" value="{{ old('melee_entries_json', $meleeEntriesJson) }}">

    {{-- Pills Container --}}
    <div class="melee-pills-container" id="melee_pills_container"></div>

    {{-- Search Select2 (no button — Enter / selection triggers add) --}}
    <select id="melee_search_select" class="form-control" style="width: 100%">
        <option value="">Search Melee Diamond...</option>
    </select>

    {{-- Aggregate Totals --}}
    <div class="melee-aggregate-row" id="melee_aggregate_row" style="display:none;">
        <div class="melee-aggregate-item">
            <label><i class="fas fa-balance-scale"></i> Total Carat Weight</label>
            <input type="text" id="melee_total_carat_display" readonly placeholder="0.000" value="">
        </div>
        <div class="melee-aggregate-item">
            <label><i class="fas fa-dollar-sign"></i> Total Price ($)</label>
            <input type="text" id="melee_total_price_display" readonly placeholder="$0.00" value="">
        </div>
    </div>
</div>

<script>
/**
 * Multi Melee Manager — tag/pill style identical to MultiSkuManager
 * Manages multiple melee diamond entries with per-entry pieces and price.
 * Uses Select2 AJAX for searching melee inventory.
 */
(function() {
    'use strict';

    const MultiMeleeManager = {
        entries: [],         // Array of {melee_diamond_id, name, pieces, avg_carat_per_piece, price_per_ct, available_pieces}
        select2Initialized: false,

        init() {
            this.container = document.getElementById('melee_pills_container');
            this.jsonInput = document.getElementById('melee_entries_json');
            this.searchSelect = document.getElementById('melee_search_select');
            this.aggregateRow = document.getElementById('melee_aggregate_row');
            this.totalCaratDisplay = document.getElementById('melee_total_carat_display');
            this.totalPriceDisplay = document.getElementById('melee_total_price_display');

            if (!this.container || !this.searchSelect) return;

            this.initSelect2();
            this.bindEvents();
            this.loadExisting();
        },

        initSelect2() {
            const self = this;
            const $select = $(this.searchSelect);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                placeholder: 'Search Melee Diamond...',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("melee.search") }}',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return { term: params.term || '' };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text,
                                    available_pieces: item.available_pieces,
                                    category_name: item.category_name,
                                    price: item.price,
                                    avg_carat_per_piece: item.avg_carat_per_piece
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            this.select2Initialized = true;
        },

        bindEvents() {
            const self = this;

            // Add on select2 selection (Enter key or click in dropdown)
            $(this.searchSelect).on('select2:select', function(e) {
                self.addFromSelect2();
            });
        },

        addFromSelect2() {
            const $select = $(this.searchSelect);
            const data = $select.select2('data');
            if (!data || data.length === 0 || !data[0].id) {
                this.showNotification('Please select a melee diamond first', 'warning');
                return;
            }

            const item = data[0];
            const meleeId = parseInt(item.id);

            // Check duplicate
            if (this.entries.find(e => e.melee_diamond_id === meleeId)) {
                this.showNotification('This melee diamond is already added', 'warning');
                $select.val(null).trigger('change');
                return;
            }

            // Add entry with default 1 piece
            this.entries.push({
                melee_diamond_id: meleeId,
                name: item.text || item.category_name || ('Melee #' + meleeId),
                pieces: 1,
                avg_carat_per_piece: parseFloat(item.avg_carat_per_piece) || 0,
                price_per_ct: parseFloat(item.price) || 0,
                available_pieces: parseInt(item.available_pieces) || 0
            });

            this.addPill(this.entries[this.entries.length - 1], this.entries.length - 1);
            this.updateHiddenInputs();
            this.updateAggregates();

            // Clear select2
            $select.val(null).trigger('change');
        },

        addPill(entry, index) {
            const pill = document.createElement('div');
            pill.className = 'melee-pill';
            pill.dataset.index = index;
            pill.dataset.meleeId = entry.melee_diamond_id;

            const carat = (entry.pieces * entry.avg_carat_per_piece).toFixed(3);
            const totalPrice = (carat * entry.price_per_ct).toFixed(2);

            pill.innerHTML = `
                <span class="melee-pill-name" title="${this.escapeHtml(entry.name)}">${this.escapeHtml(this.truncateName(entry.name))}</span>
                <div class="melee-pill-pieces-wrapper">
                    <label>Pcs:</label>
                    <input type="number" class="melee-pill-pieces-input" value="${entry.pieces}" min="1" max="${entry.available_pieces || 9999}" data-index="${index}">
                </div>
                <span class="melee-pill-price" title="$${entry.price_per_ct}/ct × ${carat}ct">$${totalPrice}</span>
                <span class="melee-pill-details">${carat}ct</span>
                <button type="button" class="melee-pill-remove" data-index="${index}" title="Remove">×</button>
            `;

            // Bind pieces input change
            const piecesInput = pill.querySelector('.melee-pill-pieces-input');
            piecesInput.addEventListener('input', (e) => this.onPiecesChange(e, index));
            piecesInput.addEventListener('change', (e) => this.onPiecesChange(e, index));

            // Bind remove
            pill.querySelector('.melee-pill-remove').addEventListener('click', (e) => {
                e.preventDefault();
                this.removeEntry(index);
            });

            this.container.appendChild(pill);
            this.container.classList.add('has-pills');
        },

        onPiecesChange(e, index) {
            let val = parseInt(e.target.value) || 0;
            if (val < 1) val = 1;

            const entry = this.entries[index];
            if (!entry) return;

            // Cap at available pieces
            if (entry.available_pieces > 0 && val > entry.available_pieces) {
                val = entry.available_pieces;
                e.target.value = val;
                this.showNotification(`Max available: ${entry.available_pieces} pieces`, 'warning');
            }

            entry.pieces = val;

            // Update pill display
            const pill = this.container.querySelector(`.melee-pill[data-index="${index}"]`);
            if (pill) {
                const carat = (val * entry.avg_carat_per_piece).toFixed(3);
                const totalPrice = (carat * entry.price_per_ct).toFixed(2);
                pill.querySelector('.melee-pill-price').textContent = '$' + totalPrice;
                pill.querySelector('.melee-pill-price').title = `$${entry.price_per_ct}/ct × ${carat}ct`;
                pill.querySelector('.melee-pill-details').textContent = carat + 'ct';
            }

            this.updateHiddenInputs();
            this.updateAggregates();
        },

        removeEntry(index) {
            this.entries.splice(index, 1);
            this.rebuildPills();
            this.updateHiddenInputs();
            this.updateAggregates();
        },

        rebuildPills() {
            this.container.innerHTML = '';
            if (this.entries.length === 0) {
                this.container.classList.remove('has-pills');
            } else {
                this.entries.forEach((entry, i) => this.addPill(entry, i));
            }
        },

        updateHiddenInputs() {
            // Update JSON hidden input
            const data = this.entries.map(e => ({
                melee_diamond_id: e.melee_diamond_id,
                name: e.name,
                pieces: e.pieces,
                avg_carat_per_piece: e.avg_carat_per_piece,
                price_per_ct: e.price_per_ct,
                available_pieces: e.available_pieces
            }));
            this.jsonInput.value = JSON.stringify(data);

            // Remove old individual hidden inputs
            const form = this.jsonInput.closest('form');
            if (form) {
                form.querySelectorAll('input[name^="melee_entries["]').forEach(el => el.remove());

                // Create individual hidden inputs for form submission
                data.forEach((entry, i) => {
                    const fields = ['melee_diamond_id', 'pieces', 'avg_carat_per_piece', 'price_per_ct'];
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `melee_entries[${i}][${field}]`;
                        input.value = entry[field];
                        form.appendChild(input);
                    });
                });
            }

            // Backward compatibility: set old single melee fields from first entry
            this.setBackwardCompatFields();
        },

        setBackwardCompatFields() {
            // For backward compat, populate the old single-melee hidden fields if they exist
            const form = this.jsonInput.closest('form');
            if (!form) return;

            if (this.entries.length > 0) {
                const first = this.entries[0];
                const aggCarat = this.entries.reduce((sum, e) => sum + (e.pieces * e.avg_carat_per_piece), 0);
                this.setFieldValue(form, 'melee_diamond_id', first.melee_diamond_id);
                this.setFieldValue(form, 'melee_pieces', this.entries.reduce((s, e) => s + e.pieces, 0));
                this.setFieldValue(form, 'melee_carat', aggCarat.toFixed(3));
                this.setFieldValue(form, 'melee_price_per_ct', first.price_per_ct);
            } else {
                this.setFieldValue(form, 'melee_diamond_id', '');
                this.setFieldValue(form, 'melee_pieces', '');
                this.setFieldValue(form, 'melee_carat', '');
                this.setFieldValue(form, 'melee_price_per_ct', '');
            }
        },

        setFieldValue(form, name, value) {
            let el = form.querySelector(`[name="${name}"]`);
            if (!el) {
                // Create hidden input for backward compat
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                form.appendChild(el);
            }
            el.value = value;
        },

        updateAggregates() {
            let totalCarat = 0;
            let totalPrice = 0;
            this.entries.forEach(e => {
                const carat = e.pieces * e.avg_carat_per_piece;
                totalCarat += carat;
                totalPrice += carat * e.price_per_ct;
            });
            if (this.totalCaratDisplay) this.totalCaratDisplay.value = totalCarat > 0 ? totalCarat.toFixed(3) : '';
            if (this.totalPriceDisplay) this.totalPriceDisplay.value = totalPrice > 0 ? '$' + totalPrice.toFixed(2) : '';

            // Show/hide aggregate row
            if (this.aggregateRow) {
                this.aggregateRow.style.display = this.entries.length > 0 ? 'flex' : 'none';
            }
        },

        loadExisting() {
            try {
                const raw = this.jsonInput.value;
                if (!raw || raw === '[]') return;

                const data = JSON.parse(raw);
                if (!Array.isArray(data) || data.length === 0) return;

                data.forEach(entry => {
                    this.entries.push({
                        melee_diamond_id: parseInt(entry.melee_diamond_id),
                        name: entry.name || ('Melee #' + entry.melee_diamond_id),
                        pieces: parseInt(entry.pieces) || 1,
                        avg_carat_per_piece: parseFloat(entry.avg_carat_per_piece) || 0,
                        price_per_ct: parseFloat(entry.price_per_ct) || 0,
                        available_pieces: parseInt(entry.available_pieces) || 0
                    });
                });

                this.rebuildPills();
                this.updateHiddenInputs();
                this.updateAggregates();
            } catch (e) {
                console.error('[MultiMelee] Error loading existing entries:', e);
            }
        },

        truncateName(name) {
            return name.length > 35 ? name.substring(0, 32) + '...' : name;
        },

        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        showNotification(message, type) {
            // Use existing toastr if available
            if (typeof toastr !== 'undefined') {
                toastr[type === 'warning' ? 'warning' : 'info'](message);
                return;
            }
            // Fallback inline notification
            const notif = document.createElement('div');
            notif.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;color:white;font-size:13px;z-index:10000;opacity:0;transition:opacity .3s;' +
                (type === 'warning' ? 'background:#f59e0b;' : 'background:#6366f1;');
            notif.textContent = message;
            document.body.appendChild(notif);
            requestAnimationFrame(() => { notif.style.opacity = '1'; });
            setTimeout(() => {
                notif.style.opacity = '0';
                setTimeout(() => notif.remove(), 300);
            }, 3000);
        }
    };

    // Initialize immediately if DOM ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => MultiMeleeManager.init());
    } else {
        // Small delay for AJAX-loaded partials
        setTimeout(() => MultiMeleeManager.init(), 100);
    }

    // Expose for re-initialization from AJAX partial loads
    window.initMultiMeleeManager = function() {
        MultiMeleeManager.entries = [];
        MultiMeleeManager.select2Initialized = false;
        MultiMeleeManager.init();
    };
})();
</script>
