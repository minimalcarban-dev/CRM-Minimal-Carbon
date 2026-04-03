{{-- Multi Melee Diamond Selector - Clean Minimal Theme --}}
<style>
    /* ========================================
       SELECT2 INPUT - CLEAN THEME
       ======================================== */

    .melee-search-select {
        width: 100%;
    }

    #melee_search_select+.select2-container {
        width: 100% !important;
    }

    /* Main Input Container - Clean Style */
    #melee_search_select+.select2-container .select2-selection--multiple {
        display: flex !important;
        align-items: center !important;
        min-height: 46px !important;
        height: 46px !important;
        padding: 0 10px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        background: #ffffff !important;
        box-shadow: none !important;
        transition: all 0.15s ease !important;
        overflow: hidden !important;
    }

    /* Focus State */
    #melee_search_select+.select2-container.select2-container--focus .select2-selection--multiple,
    #melee_search_select+.select2-container.select2-container--open .select2-selection--multiple {
        border-color: #6366f1 !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    }

    /* Rendered Container */
    #melee_search_select+.select2-container .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
        overflow: hidden !important;
        text-align: left !important;
    }

    /* Hide selection tags */
    #melee_search_select+.select2-container .select2-selection--multiple .select2-selection__choice {
        display: none !important;
    }

    /* Search inline container - KEEP AT START */
    #melee_search_select+.select2-container .select2-selection--multiple .select2-search--inline {
        display: flex !important;
        align-items: center !important;
        order: -1 !important;
        /* This keeps search at the beginning */
        flex: 1 1 auto !important;
        float: none !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
        direction: ltr !important;
    }

    /* Search Input Field */
    #melee_search_select+.select2-container .select2-selection--multiple .select2-search__field {
        position: static !important;
        left: auto !important;
        right: auto !important;
        transform: none !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        height: 36px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        outline: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
        color: #374151 !important;
        font-size: 0.875rem !important;
        line-height: 36px !important;
        text-align: left !important;
        direction: ltr !important;
        unicode-bidi: plaintext !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    #melee_search_select+.select2-container .select2-selection--multiple .select2-search__field::placeholder {
        color: #9ca3af !important;
        opacity: 1 !important;
    }

    /* ========================================
       SELECT2 DROPDOWN - CLEAN STYLE
       ======================================== */

    .melee-select2.select2-container {
        width: 100% !important;
    }

    .melee-select2 .select2-selection--single,
    .melee-select2 .select2-selection--multiple {
        height: 46px !important;
        min-height: 46px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        background: #ffffff !important;
        box-shadow: none !important;
        transition: all 0.15s ease;
    }

    .melee-select2.select2-container--focus .select2-selection--single,
    .melee-select2.select2-container--open .select2-selection--single {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    }

    .melee-select2-dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        overflow: hidden !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1) !important;
        margin-top: 4px !important;
    }

    .melee-select2-dropdown .select2-search--dropdown {
        display: none !important;
    }

    .melee-select2-dropdown .select2-results {
        max-height: none !important;
        overflow: visible !important;
        padding: 4px !important;
    }

    .melee-select2-dropdown .select2-results__options {
        max-height: 300px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .melee-select2-dropdown .select2-results__option {
        padding: 10px 12px !important;
        color: #374151 !important;
        font-size: 0.875rem !important;
        border-radius: 4px !important;
        margin-bottom: 2px !important;
        cursor: pointer !important;
        transition: background-color 0.15s ease !important;
    }

    .melee-select2-dropdown .select2-results__option:hover {
        background: #f3f4f6 !important;
    }

    .melee-select2-dropdown .select2-results__option--highlighted.select2-results__option--selectable {
        background: #eef2ff !important;
        color: #4f46e5 !important;
    }

    .melee-select2-dropdown .select2-results__option--selected {
        background: #6366f1 !important;
        color: #ffffff !important;
        font-weight: 500 !important;
    }

    /* ========================================
       DARK THEME SUPPORT
       ======================================== */

    [data-theme="dark"] .melee-select2 .select2-selection--single,
    [data-theme="dark"] .melee-select2 .select2-selection--multiple,
    [data-theme="dark"] #melee_search_select+.select2-container .select2-selection--multiple {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    [data-theme="dark"] .melee-select2 .select2-selection__rendered,
    [data-theme="dark"] .melee-select2 .select2-selection--multiple .select2-search__field,
    [data-theme="dark"] #melee_search_select+.select2-container .select2-selection--multiple .select2-search__field {
        color: #e2e8f0 !important;
    }

    [data-theme="dark"] .melee-select2-dropdown {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    [data-theme="dark"] .melee-select2-dropdown .select2-results__option {
        color: #e2e8f0 !important;
    }

    [data-theme="dark"] .melee-select2-dropdown .select2-results__option--selected {
        background: #1e293b !important;
    }

    /* ========================================
        MELEE PILLS - CLEAN MINIMAL DESIGN
       ======================================== */

    .melee-pills-container {
        display: none;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 12px;
        padding: 0;
    }

    .melee-pills-container.has-pills {
        display: flex;
        padding: 14px;
        /* background: #fafbfc; Lighter background */
        /* border-radius: 8px; */
        /* border: 1px solid #e5e7eb; */
    }

    /* Individual Pill - Rounded Pill Style (matching SKU) */
    .melee-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 5px 10px;
        background: #f8f9ff;
        border: 1px solid #e0e7ff;
        border-radius: 24px;
        /* Rounded pill shape like SKU */
        font-size: 0.8125rem;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        flex-wrap: nowrap;
        min-width: 0;
    }

    .melee-pill:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.1);
        background: #eef2ff;
    }

    /* Name Badge - Rounded Badge Style */
    .melee-pill-name {
        font-weight: 600;
        font-size: 0.8125rem;
        color: #4f46e5;
        background: #eef2ff;
        padding: 5px 12px;
        border-radius: 16px;
        /* Rounded badge */
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Details Text - Clean Style */
    .melee-pill-details {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Stock Display - Rounded Badge Style */
    .melee-pill-stock {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 500;
        white-space: nowrap;
        flex-shrink: 0;
        padding: 4px 10px;
        background: #f3f4f6;
        border-radius: 12px;
        /* Rounded badge */
    }

    /* Pieces Input Wrapper - Rounded Modern Style */
    .melee-pill-pieces-wrapper {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        /* Rounded style */
        padding: 5px 10px;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    .melee-pill-pieces-wrapper:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .melee-pill-pieces-wrapper label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        margin: 0;
        margin-right: 6px;
        white-space: nowrap;
    }

    .melee-pill-pieces-input {
        width: 45px;
        padding: 2px 4px;
        border: none;
        background: transparent;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        text-align: right;
        outline: none;
        -moz-appearance: textfield;
    }

    .melee-pill-pieces-input::-webkit-outer-spin-button,
    .melee-pill-pieces-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Price Display - Rounded Modern Style */
    .melee-pill-price {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 5px 12px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 12px;
        /* Rounded badge */
        font-size: 0.8125rem;
        color: #059669;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Remove Button - Circular Style */
    .melee-pill-remove {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        /* Circular button */
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.15s ease;
        padding: 0;
        flex-shrink: 0;
        margin-left: 4px;
    }

    .melee-pill-remove:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* ========================================
       AGGREGATE ROW - CLEAN STYLE
       ======================================== */

    .melee-aggregate-row {
        display: flex;
        gap: 12px;
        margin-top: 12px;
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
        border-radius: 6px;
        font-size: 0.8125rem;
        background: #f9fafb;
        color: #374151;
        font-weight: 500;
    }

    /* ========================================
       RESPONSIVE DESIGN
       ======================================== */

    @media (max-width: 768px) {
        .melee-pill {
            flex-wrap: wrap;
            /* Allow wrapping on mobile */
            width: 100%;
            /* Full width on mobile */
        }

        .melee-pill-name {
            flex-basis: 100%;
            /* Full width on mobile */
            margin-bottom: 6px;
            /* Space below name */
        }

        .melee-aggregate-row {
            flex-direction: column;
        }

        .melee-aggregate-item {
            min-width: 100%;
        }
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
                $entries = is_array($order->melee_entries) ? $order->melee_entries : [];
                $meleeIds = collect($entries)->pluck('melee_diamond_id')->filter()->map(fn($id) => (int) $id)->all();
                $melees = \App\Models\MeleeDiamond::with('category')->whereIn('id', $meleeIds)->get()->keyBy('id');

                // Editing existing order: allow current order reservation + live available stock.
                $reservedByMeleeId = collect($entries)
                    ->groupBy(fn($e) => (int) ($e['melee_diamond_id'] ?? 0))
                    ->map(fn($rows) => (int) collect($rows)->sum(fn($r) => (int) ($r['pieces'] ?? 0)))
                    ->all();

                $entries = array_map(function ($e) use ($melees, $reservedByMeleeId) {
                    $meleeId = (int) ($e['melee_diamond_id'] ?? 0);
                    if (isset($melees[$meleeId])) {
                        $d = $melees[$meleeId];
                        if (empty($e['name'])) {
                            $typeLabel = optional($d->category)->type === 'lab_grown' ? 'Lab Grown' : 'Natural';
                            $e['name'] =
                                "[{$typeLabel}] " . optional($d->category)->name . " - {$d->shape} - {$d->size_label}";
                        }

                        $reserved = (int) ($reservedByMeleeId[$meleeId] ?? 0);
                        $e['available_pieces'] = (int) $d->available_pieces + $reserved;
                    }

                    return $e;
                }, $entries);

                $meleeEntriesJson = json_encode(array_values($entries));
            } elseif ($order->melee_diamond_id) {
                // Backward compat: build entry from old single-melee columns
                $md = $order->meleeDiamond;
                $name = 'Melee #' . $order->melee_diamond_id;
                if ($md && $md->category) {
                    $typeLabel = $md->category->type === 'lab_grown' ? 'Lab Grown' : 'Natural';
                    $name = "[{$typeLabel}] {$md->category->name} - {$md->shape} - {$md->size_label}";
                }
                $pieces = (int) ($order->melee_pieces ?? 0);
                $meleeEntriesJson = json_encode([
                    [
                        'melee_diamond_id' => $order->melee_diamond_id,
                        'name' => $name,
                        'pieces' => $pieces,
                        'avg_carat_per_piece' =>
                            $order->melee_carat && $pieces > 0 ? round($order->melee_carat / $pieces, 5) : 0,
                        'price_per_ct' => (float) ($order->melee_price_per_ct ?? 0),
                        'available_pieces' => $md ? (int) $md->available_pieces + $pieces : $pieces,
                    ],
                ]);
            }
        }
    @endphp

    {{-- Hidden input storing JSON array of all melee entries --}}
    <input type="hidden" name="melee_entries_json" id="melee_entries_json"
        value="{{ old('melee_entries_json', $meleeEntriesJson) }}">

    {{-- Pills Container --}}
    <div class="melee-pills-container" id="melee_pills_container"></div>

    {{-- Search Select2 (no button — Enter / selection triggers add) --}}
    <select id="melee_search_select" class="form-control-modern melee-search-select" multiple></select>

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
    (function () {
        'use strict';

        const MultiMeleeManager = {
            entries: [], // Array of {melee_diamond_id, name, pieces, avg_carat_per_piece, price_per_ct, available_pieces}
            select2Initialized: false,
            overstockAlertShownByMeleeId: {},

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
                const jq = window.jQuery || window.$;
                if (!jq || !jq.fn || !jq.fn.select2) {
                    console.warn(
                        '[MultiMelee] Select2 not available; melee search will use basic select style.');
                    return;
                }

                const self = this;
                this.$jq = jq;
                const $select = jq(this.searchSelect);
                $select.prop('multiple', true);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search Melee Diamond...',
                    allowClear: true,
                    closeOnSelect: true,
                    minimumInputLength: 0,
                    width: '100%',
                    selectionCssClass: 'melee-select2-selection',
                    containerCssClass: 'melee-select2',
                    dropdownCssClass: 'melee-select2-dropdown',
                    ajax: {
                        url: '{{ route('melee.search') }}',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return {
                                term: params.term || '',
                                limit: 10
                            };
                        },
                        processResults: function (data) {
                            const rows = Array.isArray(data) ? data : [];
                            return {
                                results: rows.map(function (item) {
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

                // Safety pass: keep search field left-aligned within the input shell.
                const normalizeSearchField = () => {
                    const field = document.querySelector(
                        '.melee-select2-selection .select2-search__field'
                    );
                    const searchInline = document.querySelector(
                        '.melee-select2-selection .select2-search--inline'
                    );
                    const rendered = document.querySelector(
                        '.melee-select2-selection .select2-selection__rendered'
                    );

                    if (rendered) {
                        rendered.style.display = 'flex';
                        rendered.style.alignItems = 'center';
                        rendered.style.justifyContent = 'flex-start';
                        rendered.style.flexWrap = 'nowrap';
                        rendered.style.textAlign = 'left';
                        rendered.style.lineHeight = 'normal';
                    }

                    if (searchInline) {
                        searchInline.style.display = 'flex';
                        searchInline.style.alignItems = 'center';
                        searchInline.style.float = 'none';
                        searchInline.style.width = '100%';
                        searchInline.style.margin = '0';
                        searchInline.style.marginLeft = '0';
                        searchInline.style.marginRight = 'auto';
                        searchInline.style.padding = '0';
                        searchInline.style.textAlign = 'left';
                        searchInline.style.direction = 'ltr';
                    }

                    if (field) {
                        field.style.textAlign = 'left';
                        field.style.width = '100%';
                        field.style.minWidth = '0';
                        field.style.marginLeft = '0';
                        field.style.paddingLeft = '2px';
                        field.style.position = 'static';
                        field.style.right = 'auto';
                        field.style.transform = 'none';
                        field.style.direction = 'ltr';
                        field.style.unicodeBidi = 'plaintext';
                    }
                };

                normalizeSearchField();
                $select.on('select2:open', normalizeSearchField);
                $select.on('select2:close', normalizeSearchField);
                $select.on('change', normalizeSearchField);

                this.select2Initialized = true;
            },

            bindEvents() {
                const self = this;

                // Add on select2 selection (Enter key or click in dropdown)
                if (this.$jq && this.$jq.fn && this.$jq.fn.select2) {
                    this.$jq(this.searchSelect).on('select2:select', function (e) {
                        self.addFromSelect2(e && e.params ? e.params.data : null);
                    });
                }

                // Submit time par sirf hidden flag sync karo; warning real-time on input change.
                const form = this.jsonInput ? this.jsonInput.closest('form') : null;
                if (form && !form.dataset.meleeValidationBound) {
                    form.dataset.meleeValidationBound = '1';
                    form.addEventListener('submit', (e) => {
                        if (!this.validateEntriesBeforeSubmit()) {
                            e.preventDefault();
                        }
                    });
                }
            },

            addFromSelect2(selectedData = null) {
                const jq = this.$jq || window.jQuery || window.$;
                const $select = jq(this.searchSelect);
                const data = selectedData && selectedData.id ? selectedData : (($select.select2('data') || [])[
                    0] || null);
                if (!data || !data.id) {
                    this.showNotification('Please select a melee diamond first', 'warning');
                    return;
                }

                const item = data;
                const meleeId = parseInt(item.id);
                const availablePieces = parseInt(item.available_pieces) || 0;

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
                    available_pieces: availablePieces
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
                    <input type="number" class="melee-pill-pieces-input" value="${entry.pieces}" min="1" data-index="${index}">
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
                const available = parseInt(entry.available_pieces) || 0;
                const meleeId = parseInt(entry.melee_diamond_id) || 0;
                const isOverstock = val > available;

                if (isOverstock) {
                    e.target.setCustomValidity('');

                    // Sirf threshold cross hone par SweetAlert show karo (har keypress par nahi).
                    if (
                        meleeId > 0 &&
                        !this.overstockAlertShownByMeleeId[meleeId]
                    ) {
                        this.showOverstockSweetAlert(entry, available, val);
                        this.overstockAlertShownByMeleeId[meleeId] = true;
                    }
                } else {
                    e.target.setCustomValidity('');
                    if (meleeId > 0) {
                        this.overstockAlertShownByMeleeId[meleeId] = false;
                    }
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
                this.syncAllowNegativeMeleeFlag();
            },

            validateEntriesBeforeSubmit() {
                this.syncAllowNegativeMeleeFlag();
                return true;
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
                        const fields = ['melee_diamond_id', 'pieces', 'avg_carat_per_piece',
                            'price_per_ct'
                        ];
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
                this.syncAllowNegativeMeleeFlag();
            },

            setBackwardCompatFields() {
                // For backward compat, populate the old single-melee hidden fields if they exist
                const form = this.jsonInput.closest('form');
                if (!form) return;

                if (this.entries.length > 0) {
                    const first = this.entries[0];
                    const aggCarat = this.entries.reduce((sum, e) => sum + (e.pieces * e.avg_carat_per_piece),
                        0);
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
                if (this.totalCaratDisplay) this.totalCaratDisplay.value = totalCarat > 0 ? totalCarat.toFixed(
                    3) : '';
                if (this.totalPriceDisplay) this.totalPriceDisplay.value = totalPrice > 0 ? '$' + totalPrice
                    .toFixed(2) : '';

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
                    this.syncAllowNegativeMeleeFlag();
                } catch (e) {
                    console.error('[MultiMelee] Error loading existing entries:', e);
                }
            },

            syncAllowNegativeMeleeFlag() {
                const form = this.jsonInput ? this.jsonInput.closest('form') : null;
                if (!form) return;

                const hasOverstock = this.entries.some((entry) => {
                    const pieces = parseInt(entry.pieces) || 0;
                    const available = parseInt(entry.available_pieces) || 0;
                    return pieces > available;
                });

                this.setFieldValue(form, 'allow_negative_melee', hasOverstock ? '1' : '0');
            },

            showOverstockSweetAlert(entry, available, requested) {
                const shortage = Math.max(0, requested - available);
                const lotName = this.escapeHtml(entry.name || 'Selected Melee Lot');

                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Stock Limit Crossed',
                        html: `
                                <div style="text-align:left; font-family:system-ui;">

                                    <!-- Lot Name -->
                                    <div style="font-size:16px; font-weight:600; margin-bottom:10px;">
                                        📦 ${lotName}
                                    </div>

                                    <!-- Stats Card -->
                                    <div style="
                                        display:flex;
                                        justify-content:space-between;
                                        gap:10px;
                                        margin-bottom:12px;
                                    ">
                                        <div style="
                                            flex:1;
                                            background:#f1f5f9;
                                            padding:10px;
                                            border-radius:10px;
                                            text-align:center;
                                        ">
                                            <div style="font-size:12px;color:#64748b;">Available</div>
                                            <div style="font-size:18px;font-weight:600;">${available}</div>
                                        </div>

                                        <div style="
                                            flex:1;
                                            background:#fef3c7;
                                            padding:10px;
                                            border-radius:10px;
                                            text-align:center;
                                        ">
                                            <div style="font-size:12px;color:#92400e;">Requested</div>
                                            <div style="font-size:18px;font-weight:600;">${requested}</div>
                                        </div>

                                        <div style="
                                            flex:1;
                                            background:#fee2e2;
                                            padding:10px;
                                            border-radius:10px;
                                            text-align:center;
                                        ">
                                            <div style="font-size:12px;color:#991b1b;">Extra</div>
                                            <div style="font-size:18px;font-weight:600;">${shortage}</div>
                                        </div>
                                    </div>

                                    <!-- Warning Box -->
                                    <div style="
                                        background:#fff7ed;
                                        border:1px solid #fdba74;
                                        padding:10px;
                                        border-radius:10px;
                                        font-size:13px;
                                        color:#9a3412;
                                    ">
                                        ⚠️ Saving this order with the current quantity will result in <b>negative stock</b>.
                                    </div>

                                </div>
                            `,
                        confirmButtonText: 'Proceed Anyway',
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#DB1A1A',
                        cancelButtonColor: '#64748b',
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-2xl shadow-xl'
                        }
                    });
                    return;
                }

                this.showNotification(
                    `Stock limit crossed for ${entry.name}. Available ${available}, requested ${requested}. Saving with this quantity will make <b>stock negative</b>.`,
                    'warning'
                );
            },

            truncateName(name) {
                return name.length > 35 ? name.substring(0, 60) + '...' : name;
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
                notif.style.cssText =
                    'position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;color:white;font-size:13px;z-index:10000;opacity:0;transition:opacity .3s;' +
                    (type === 'warning' ? 'background:#f59e0b;' : 'background:#6366f1;');
                notif.textContent = message;
                document.body.appendChild(notif);
                requestAnimationFrame(() => {
                    notif.style.opacity = '1';
                });
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
        window.initMultiMeleeManager = function () {
            MultiMeleeManager.entries = [];
            MultiMeleeManager.select2Initialized = false;
            MultiMeleeManager.init();
        };
    })();
</script>