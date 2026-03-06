@csrf
<div class="form-section-card">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon"><i class="bi bi-file-text"></i></div>
            <div class="section-text">
                <h5 class="section-title">Invoice Information</h5>
                <p class="section-description">Enter basic invoice details</p>
            </div>
        </div>
    </div>
    <div class="section-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        Invoice No
                    </label>
                    <input type="text" name="invoice_no" class="form-control" required
                        value="{{ old('invoice_no', $invoice->invoice_no ?? '') }}" placeholder="INV-001">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Date
                    </label>
                    <input type="date" name="invoice_date" class="form-control" required
                        value="{{ old('invoice_date', $invoice->invoice_date ?? date('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Invoice Type
                    </label>
                    <select name="invoice_type" class="form-select">
                        <option value="tax" {{ (old('invoice_type', $invoice->invoice_type ?? 'tax') == 'tax') ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="proforma" {{ (old('invoice_type', $invoice->invoice_type ?? '') == 'proforma') ? 'selected' : '' }}>Proforma Invoice</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Copy Type
                    </label>
                    <select name="copy_type" class="form-select">
                        <option value="">-- None --</option>
                        <option value="original" {{ (old('copy_type', $invoice->copy_type ?? '') == 'original') ? 'selected' : '' }}>Original - Recipient</option>
                        <option value="duplicate" {{ (old('copy_type', $invoice->copy_type ?? '') == 'duplicate') ? 'selected' : '' }}>Duplicate - Transporter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Invoice Region
                    </label>
                    <select name="invoice_region" class="form-select">
                        <option value="">-- Select Region --</option>
                        @foreach(\App\Models\Invoice::REGIONS as $code => $data)
                            <option value="{{ $code }}" {{ (old('invoice_region', $invoice->invoice_region ?? '') == $code) ? 'selected' : '' }}>
                                {{ $data['flag'] }} {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid" style="margin-top: 1rem;">
                <div class="form-group">
                    <label class="form-label">
                        Company
                    </label>
                    <select id="company_select" name="company_id" class="form-select" required>
                        <option value="">-- Select Company --</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ (old('company_id', $invoice->company_id ?? '') == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Billed To (Party)
                    </label>
                    <div class="input-group">
                        <select id="billed_select" name="billed_to_id" class="form-select">
                            <option value="">-- Select Party --</option>
                            @foreach($parties as $p)
                                <option value="{{ $p->id }}" {{ (old('billed_to_id', $invoice->billed_to_id ?? '') == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="btn_add_billed" class="btn btn-outline-secondary">Add</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Shipped To (Party)
                    </label>
                    <div class="input-group">
                        <select id="shipped_select" name="shipped_to_id" class="form-select">
                            <option value="">-- Select Party --</option>
                            @foreach($parties as $p)
                                <option value="{{ $p->id }}" {{ (old('shipped_to_id', $invoice->shipped_to_id ?? '') == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="btn_add_shipped" class="btn btn-outline-secondary">Add</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Place of Supply (State Code)
                    </label>
                    <input type="text" name="place_of_supply" id="place_of_supply" class="form-control"
                        placeholder="e.g., 07" value="{{ old('place_of_supply', $invoice->place_of_supply ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Company Details Card -->
    <div class="form-section-card">
        <div class="section-header">
            <div class="section-info">
                <div class="section-icon"><i class="bi bi-info-circle"></i></div>
                <div class="section-text">
                    <h5 class="section-title">Company Details</h5>
                    <p class="section-description">Auto-filled from selected company</p>
                </div>
            </div>
        </div>
        <div class="section-body">
            <div id="company_details" class="company-details-grid">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-file-text"></i>
                        GST Number
                    </div>
                    <div class="detail-value" id="company_gst">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-geo"></i>
                        Address
                    </div>
                    <div class="detail-value" id="company_address">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="bi bi-bank"></i>
                        Bank Details
                    </div>
                    <div class="detail-value" id="company_bank">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Card -->
    <div class="form-section-card">
        <div class="section-header">
            <div class="section-info">
                <div class="section-icon"><i class="bi bi-list-ul"></i></div>
                <div class="section-text">
                    <h5 class="section-title">Invoice Items</h5>
                    <p class="section-description">Add items to the invoice</p>
                </div>
            </div>
            <button type="button" id="add_row" class="btn-primary-custom" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                <i class="bi bi-plus-circle"></i>
                Add Item
            </button>
        </div>
        <div class="section-body">
            <div class="table-responsive">
                    <table class="tracker-table" id="items_table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>HSN Code</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Rate</th>
                                <th>Amount</th>
                                <th style="width: 60px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(old('items'))
                                @foreach(old('items') as $i => $it)
                                    <tr class="table-row">
                                        <td><input name="items[{{$i}}][description_of_goods]" class="form-control"
                                                value="{{ $it['description_of_goods'] ?? '' }}" placeholder="Enter description">
                                        </td>
                                        <td><input name="items[{{$i}}][hsn_code]" class="form-control"
                                                value="{{ $it['hsn_code'] ?? '' }}" placeholder="HSN"></td>
                                        <td><input type="number" step="0.01" name="items[{{$i}}][quantity]" class="form-control quantity"
                                                value="{{ $it['quantity'] ?? '' }}" placeholder="0.00"></td>
                                        <td>
                                            <select name="items[{{$i}}][unit]" class="form-select unit">
                                                <option value="pieces" {{ ($it['unit'] ?? '') == 'pieces' ? 'selected' : '' }}>Pcs</option>
                                                <option value="carats" {{ ($it['unit'] ?? '') == 'carats' ? 'selected' : '' }}>Cts</option>
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" name="items[{{$i}}][rate]" class="form-control rate"
                                                value="{{ $it['rate'] ?? '' }}" placeholder="0.00"></td>
                                        <td><input type="number" step="0.01" name="items[{{$i}}][amount]" class="form-control amount"
                                                value="{{ $it['amount'] ?? '' }}" readonly></td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-tracker-reset btn-remove remove-row" title="Remove Item" style="padding: 0.5rem;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif(isset($invoice) && $invoice->items->count())
                                @foreach($invoice->items as $i => $it)
                                    @php
                                        $isPieces = !empty($it->pieces) && empty($it->carats);
                                        $quantity = $isPieces ? $it->pieces : $it->carats;
                                        $unit = $isPieces ? 'pieces' : 'carats';
                                    @endphp
                                    <tr class="table-row">
                                        <td><input name="items[{{$i}}][description_of_goods]" class="form-control"
                                                value="{{ $it->description_of_goods }}" placeholder="Enter description"></td>
                                        <td><input name="items[{{$i}}][hsn_code]" class="form-control" value="{{ $it->hsn_code }}"
                                                placeholder="HSN"></td>
                                        <td><input type="number" step="0.01" name="items[{{$i}}][quantity]" class="form-control quantity" value="{{ $quantity }}"
                                                placeholder="0.00"></td>
                                        <td>
                                            <select name="items[{{$i}}][unit]" class="form-select unit">
                                                <option value="pieces" {{ $unit == 'pieces' ? 'selected' : '' }}>Pcs</option>
                                                <option value="carats" {{ $unit == 'carats' ? 'selected' : '' }}>Cts</option>
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" name="items[{{$i}}][rate]" class="form-control rate" value="{{ $it->rate }}"
                                                placeholder="0.00"></td>
                                        <td><input type="number" step="0.01" name="items[{{$i}}][amount]" class="form-control amount" value="{{ $it->amount }}" readonly>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-tracker-reset btn-remove remove-row" title="Remove Item" style="padding: 0.5rem;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="table-row">
                                    <td><input name="items[0][description_of_goods]" class="form-control"
                                            placeholder="Enter description"></td>
                                    <td><input name="items[0][hsn_code]" class="form-control" placeholder="HSN"></td>
                                    <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control quantity" placeholder="0.00"></td>
                                    <td>
                                        <select name="items[0][unit]" class="form-control unit">
                                            <option value="pieces">Pcs</option>
                                            <option value="carats">Cts</option>
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" name="items[0][rate]" class="form-control rate" placeholder="0.00"></td>
                                    <td><input type="number" step="0.01" name="items[0][amount]" class="form-control amount" readonly></td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-tracker-reset btn-remove remove-row" title="Remove Item" style="padding: 0.5rem;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
            </div>
        </div>
    </div>

    <!-- Tax Summary Card -->
    <div class="form-section-card">
        <div class="section-header">
            <div class="section-info">
                <div class="section-icon"><i class="bi bi-calculator"></i></div>
                <div class="section-text">
                    <h5 class="section-title">Tax Summary</h5>
                    <p class="section-description">Configure tax rates and view totals</p>
                </div>
            </div>
        </div>
        <div class="section-body">
            <!-- Tax Info Alert -->
            <div id="tax_info_alert" class="tax-info-alert">
                <i class="bi bi-info-circle"></i>
                <span id="tax_info_text">Select a company to see which taxes will apply</span>
            </div>

            <div class="form-grid" style="margin-bottom: 10px;">
                <div class="form-group" id="cgst_group">
                    <label class="form-label">CGST Rate (%)</label>
                    <input type="number" step="0.01" min="0" name="cgst_rate" id="cgst_rate" class="form-control"
                        value="{{ old('cgst_rate', '') }}" placeholder="0">
                </div>
                <div class="form-group" id="sgst_group">
                    <label class="form-label">SGST Rate (%)</label>
                    <input type="number" step="0.01" min="0" name="sgst_rate" id="sgst_rate" class="form-control"
                        value="{{ old('sgst_rate', '') }}" placeholder="0">
                </div>
                <div class="form-group" id="igst_group">
                    <label class="form-label">IGST Rate (%)</label>
                    <input type="number" step="0.01" min="0" name="igst_rate" id="igst_rate" class="form-control"
                        value="{{ old('igst_rate', '') }}" placeholder="0">
                </div>
                <div class="form-group" id="shipping_group">
                    <label class="form-label">Express Shipping</label>
                    <input type="number" step="0.01" min="0" name="express_shipping" id="express_shipping"
                        class="form-control" value="{{ old('express_shipping', $invoice->express_shipping ?? '') }}"
                        placeholder="0.00">
                </div>
            </div>

            <div class="tax-summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Taxable Amount</div>
                    <div class="summary-value" id="taxable_total">₹ 0.00</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">CGST</div>
                    <div class="summary-value" id="cgst_total">₹ 0.00</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">SGST</div>
                    <div class="summary-value" id="sgst_total">₹ 0.00</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">IGST</div>
                    <div class="summary-value" id="igst_total">₹ 0.00</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Shipping</div>
                    <div class="summary-value" id="shipping_total">₹ 0.00</div>
                </div>
                <div class="summary-item summary-total">
                    <div class="summary-label">Grand Total</div>
                    <div class="summary-value grand-total" id="grand_total">₹ 0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="tracker-form-actions" style="margin-top: 2rem;">
        <button type="submit" class="btn-primary-custom">
            <i class="bi bi-check-circle"></i>
            Save Invoice
        </button>
    </div>
</div>

<!-- Party Add Modal (reused for billed/shipped) -->
<div id="party_modal" class="modal" tabindex="-1"
    style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:1050;">
    <div style="background:#fff; padding:18px; border-radius:8px; width:720px; max-width:96%">
        <h5>Add Party</h5>
        <div id="party_form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px">
                <input name="name" class="form-input" placeholder="Name" required disabled>
                <select name="category" class="form-input" disabled>
                    <option value="">-- Select Category --</option>
                    @foreach(\App\Models\Party::CATEGORIES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input name="phone" class="form-input" placeholder="Phone" disabled>
                <input name="email" class="form-input" placeholder="Email" disabled>
                <input name="gst_no" class="form-input" placeholder="GST / Tax ID" disabled>
                <input name="pan_no" class="form-input" placeholder="PAN" disabled>
                <input name="state" class="form-input" placeholder="State" disabled>
                <input name="state_code" class="form-input" placeholder="State Code" disabled>
                <input name="country" class="form-input" placeholder="Country" value="India" disabled>
            </div>
            <div style="margin-top:8px">
                <textarea name="address" class="form-input" placeholder="Address" disabled></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">
                <button type="button" id="party_cancel" class="btn btn-secondary">Cancel</button>
                <button type="button" id="party_save" class="btn btn-primary">Save Party</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        let currentTargetSelect = null;
        const modal = document.getElementById('party_modal');
        const formWrap = document.getElementById('party_form');

        function openPartyModal(targetSelectId) {
            currentTargetSelect = targetSelectId;
            // enable inputs
            formWrap.querySelectorAll('input,textarea,select').forEach(i => i.removeAttribute('disabled'));
            modal.style.display = 'flex';
            // focus first visible input
            const first = formWrap.querySelector('[name="name"]');
            if (first) first.focus();
        }

        function closePartyModal() {
            modal.style.display = 'none';
            currentTargetSelect = null;
            // clear fields and disable
            formWrap.querySelectorAll('input,textarea,select').forEach(i => {
                if (i.name === 'country') i.value = 'India';
                else if (i.tagName === 'SELECT') i.selectedIndex = 0;
                else i.value = '';
                i.setAttribute('disabled', 'disabled');
            });
        }

        const btnAddBilled = document.getElementById('btn_add_billed');
        const btnAddShipped = document.getElementById('btn_add_shipped');
        if (btnAddBilled) btnAddBilled.addEventListener('click', () => openPartyModal('billed_select'));
        if (btnAddShipped) btnAddShipped.addEventListener('click', () => openPartyModal('shipped_select'));

        const btnCancel = document.getElementById('party_cancel');
        if (btnCancel) btnCancel.addEventListener('click', closePartyModal);

        const btnSave = document.getElementById('party_save');
        if (btnSave) {
            btnSave.addEventListener('click', function (e) {
                const nameInput = formWrap.querySelector('[name="name"]');
                if (!nameInput || !nameInput.value.trim()) {
                    alert('Please enter party name');
                    if (nameInput) nameInput.focus();
                    return;
                }

                const categoryInput = formWrap.querySelector('[name="category"]');
                if (!categoryInput || !categoryInput.value) {
                    alert('Please select a category');
                    if (categoryInput) categoryInput.focus();
                    return;
                }

                // gather data
                const payload = {};
                formWrap.querySelectorAll('input,textarea,select').forEach(el => {
                    if (el.name) payload[el.name] = el.value;
                });

                // CSRF token - read from main form hidden input
                const tokenInput = document.querySelector('input[name="_token"]');
                const token = tokenInput ? tokenInput.value : null;

                fetch('/admin/parties', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(payload)
                }).then(r => {
                    if (!r.ok) {
                        return r.json().then(err => { throw err; });
                    }
                    return r.json();
                })
                    .then(data => {
                        if (data && data.id) {
                            // Add to BOTH dropdowns so no refresh needed
                            ['billed_select', 'shipped_select'].forEach(selId => {
                                const sel = document.getElementById(selId);
                                if (sel) {
                                    const isTarget = (selId === currentTargetSelect);
                                    const opt = new Option(data.name, data.id, isTarget, isTarget);
                                    sel.add(opt);
                                }
                            });
                            closePartyModal();
                        } else {
                            alert('Failed to create party');
                            console.error('party create response', data);
                        }
                    }).catch(err => {
                        if (err && err.errors) {
                            const msgs = Object.values(err.errors).flat().join('\n');
                            alert('Validation errors:\n' + msgs);
                        } else if (err && err.message) {
                            alert('Error: ' + err.message);
                        } else {
                            alert('Error creating party. See console.');
                        }
                        console.error(err);
                    });
            });
        }

        // close modal when clicking outside content
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closePartyModal();
        });
    })();
</script>
<style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --dark: #1e293b;
        --gray: #64748b;
        --light-gray: #f1f5f9;
        --border: #e2e8f0;
        --shadow: rgba(0, 0, 0, 0.05);
        --shadow-md: rgba(0, 0, 0, 0.1);
    }

    .invoice-form-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .header-content {
        flex: 1;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.25rem 0;
    }

    .card-subtitle {
        font-size: 0.875rem;
        color: var(--gray);
        margin: 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .form-label i {
        color: var(--primary);
        font-size: 1rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: var(--light-gray);
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Company Details Grid */
    .company-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.25rem;
    }

    .detail-item {
        padding: 1rem;
        background: var(--light-gray);
        border-radius: 10px;
        border: 2px solid var(--border);
    }

    .detail-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-label i {
        color: var(--primary);
    }

    .detail-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--dark);
    }

    /* Items Table */
    .btn-add-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.875rem;
    }

    .btn-add-item:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .items-table thead {
        background: var(--light-gray);
    }

    .items-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border);
    }

    .th-action {
        width: 80px;
        text-align: center;
    }

    .items-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }

    .items-table tbody tr:hover {
        background: var(--light-gray);
    }

    .items-table td {
        padding: 0.75rem;
    }

    .input-cell {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .input-cell:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .input-cell[readonly] {
        background: var(--light-gray);
        font-weight: 600;
        color: var(--success);
    }

    .td-action {
        text-align: center;
    }

    .btn-remove {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--border);
        background: white;
        color: var(--gray);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-remove:hover {
        border-color: var(--danger);
        color: var(--danger);
        background: rgba(239, 68, 68, 0.05);
        transform: scale(1.1);
    }

    /* Tax Config */
    .tax-config-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .tax-input-group {
        display: flex;
        flex-direction: column;
    }

    .tax-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .tax-input {
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: var(--light-gray);
    }

    .tax-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Tax Summary Grid */
    .tax-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
        background: var(--light-gray);
        border-radius: 12px;
        border: 2px solid var(--border);
    }

    .summary-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .summary-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .summary-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
    }

    .summary-total {
        grid-column: 1 / -1;
        padding: 1rem;
        background: white;
        border-radius: 10px;
        border: 2px solid var(--primary);
    }

    .summary-total .summary-label {
        color: var(--primary);
        font-size: 1rem;
    }

    .grand-total {
        font-size: 1.75rem;
        color: var(--primary);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-submit {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, var(--success), #059669);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    [data-theme="dark"] .invoice-form-container,
    [data-theme="dark"] .form-card,
    [data-theme="dark"] .card-header {
        background: var(--bg-card, #1e293b) !important;
        border-color: rgba(148, 163, 184, 0.34) !important;
        box-shadow: 0 6px 18px rgba(2, 6, 23, 0.18);
    }

    [data-theme="dark"] .card-body,
    [data-theme="dark"] .table-responsive,
    [data-theme="dark"] .items-table,
    [data-theme="dark"] .tax-summary-grid,
    [data-theme="dark"] .summary-total {
        background: rgba(15, 23, 42, 0.62) !important;
        border-color: rgba(148, 163, 184, 0.3) !important;
    }

    [data-theme="dark"] .card-title,
    [data-theme="dark"] .form-label,
    [data-theme="dark"] .tax-label,
    [data-theme="dark"] .summary-value,
    [data-theme="dark"] .detail-value {
        color: var(--text-primary, #f1f5f9) !important;
    }

    [data-theme="dark"] .card-subtitle,
    [data-theme="dark"] .summary-label,
    [data-theme="dark"] .detail-label,
    [data-theme="dark"] .tax-info-alert,
    [data-theme="dark"] .text-muted {
        color: var(--text-secondary, #94a3b8) !important;
    }

    [data-theme="dark"] .form-input,
    [data-theme="dark"] .form-select,
    [data-theme="dark"] .input-cell,
    [data-theme="dark"] .tax-input {
        background: rgba(15, 23, 42, 0.62) !important;
        border-color: rgba(148, 163, 184, 0.32) !important;
        color: var(--text-primary, #f1f5f9) !important;
    }

    [data-theme="dark"] .form-input::placeholder,
    [data-theme="dark"] .input-cell::placeholder,
    [data-theme="dark"] .tax-input::placeholder {
        color: var(--text-secondary, #94a3b8) !important;
    }

    [data-theme="dark"] .form-input:focus,
    [data-theme="dark"] .form-select:focus,
    [data-theme="dark"] .input-cell:focus,
    [data-theme="dark"] .tax-input:focus {
        border-color: rgba(129, 140, 248, 0.7) !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18) !important;
    }

    [data-theme="dark"] .items-table thead,
    [data-theme="dark"] .items-table th {
        background: rgba(15, 23, 42, 0.75) !important;
        color: var(--text-secondary, #94a3b8) !important;
        border-color: rgba(148, 163, 184, 0.24) !important;
    }

    [data-theme="dark"] .items-table tbody tr:hover {
        background: rgba(99, 102, 241, 0.1) !important;
    }

    [data-theme="dark"] .btn-remove,
    [data-theme="dark"] .btn.btn-outline-secondary {
        background: rgba(255, 255, 255, 0.04) !important;
        border-color: rgba(148, 163, 184, 0.35) !important;
        color: var(--text-secondary, #94a3b8) !important;
    }

    [data-theme="dark"] .btn-add-item {
        background: linear-gradient(135deg, var(--primary, #6366f1), var(--primary-dark, #4f46e5)) !important;
        color: #fff !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .invoice-form-container {
            padding: 1rem;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .company-details-grid {
            grid-template-columns: 1fr;
        }

        .tax-config-row {
            grid-template-columns: 1fr;
        }

        .tax-summary-grid {
            grid-template-columns: 1fr;
        }

        .items-table {
            font-size: 0.85rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit {
            width: 100%;
            justify-content: center;
        }
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-card {
        animation: slideIn 0.4s ease forwards;
    }

    .item-row {
        animation: slideIn 0.3s ease forwards;
    }

    /* Tax Info Alert Styles */
    .tax-info-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 0.9rem;
        font-weight: 500;
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #7dd3fc;
    }

    .tax-info-alert.same-state {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }

    .tax-info-alert.different-state {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }

    .tax-info-alert i {
        font-size: 1.1rem;
    }

    /* Disabled Tax Group Styles */
    .tax-input-group.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .tax-input-group.disabled .tax-input {
        background: #f1f5f9;
        cursor: not-allowed;
    }

    .tax-input-group.disabled .tax-label::after {
        content: ' (N/A)';
        color: #94a3b8;
        font-size: 0.75rem;
    }

    /* Hidden class for GST fields when foreign invoice */
    .tax-input-group.hidden {
        display: none !important;
    }

    /* Foreign invoice alert styling */
    .tax-info-alert.foreign-invoice {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #f59e0b;
        color: #b45309;
    }

    /* Summary items for GST when hidden */
    #cgst_total:has(+ .hidden),
    #sgst_total:has(+ .hidden),
    #igst_total:has(+ .hidden) {
        display: none;
    }
</style>

<script>
    (function () {
        // Region data for dynamic currency symbol
        var REGIONS = {
            'IN': { name: 'India', symbol: '₹' },
            'US': { name: 'United States', symbol: '$' },
            'UK': { name: 'United Kingdom', symbol: '£' },
            'EU': { name: 'Europe', symbol: '€' },
            'CA': { name: 'Canada', symbol: 'C$' },
            'AU': { name: 'Australia', symbol: 'A$' },
            'AE': { name: 'UAE', symbol: 'د.إ' }
        };

        // Track if billed party is foreign
        var billedPartyIsForeign = false;

        // Get current currency symbol based on selected region
        function getCurrencySymbol() {
            var regionSelect = document.querySelector('[name="invoice_region"]');
            var region = regionSelect ? regionSelect.value : 'IN';
            return (REGIONS[region] && REGIONS[region].symbol) || '₹';
        }

        // Check if invoice is foreign (non-India region OR foreign party)
        function isForeignInvoice() {
            var regionSelect = document.querySelector('[name="invoice_region"]');
            var region = regionSelect ? regionSelect.value : '';
            return (region && region !== 'IN') || billedPartyIsForeign;
        }

        function recalcRow($row) {
            var quantity = parseFloat($row.querySelector('.quantity').value) || 0;
            var rate = parseFloat($row.querySelector('.rate').value) || 0;
            var amount = (quantity * rate).toFixed(2);
            $row.querySelector('.amount').value = amount;
        }

        function recalcAll() {
            var rows = document.querySelectorAll('#items_table tbody tr');
            var taxable = 0;
            rows.forEach(function (r) {
                var amt = parseFloat(r.querySelector('.amount').value) || 0;
                taxable += amt;
            });
            var currencySymbol = getCurrencySymbol();
            document.getElementById('taxable_total').innerText = currencySymbol + ' ' + taxable.toFixed(2);

            var cgst_rate = parseFloat(document.getElementById('cgst_rate').value) || 0;
            var sgst_rate = parseFloat(document.getElementById('sgst_rate').value) || 0;
            var igst_rate = parseFloat(document.getElementById('igst_rate').value) || 0;
            var shipping = parseFloat(document.getElementById('express_shipping').value) || 0;

            var place = document.getElementById('place_of_supply').value || '';
            var companyState = document.getElementById('company_gst').dataset && document.getElementById('company_gst').dataset.state || '';

            // Update tax field states and info alert
            updateTaxFieldStates(companyState, place);

            var cgst = 0, sgst = 0, igst = 0;

            // If foreign invoice, no taxes apply
            if (!isForeignInvoice()) {
                if (companyState && companyState == place) {
                    cgst = parseFloat((taxable * (cgst_rate / 100)).toFixed(2));
                    sgst = parseFloat((taxable * (sgst_rate / 100)).toFixed(2));
                } else if (companyState && place) {
                    igst = parseFloat((taxable * (igst_rate / 100)).toFixed(2));
                }
            }

            document.getElementById('cgst_total').innerText = currencySymbol + ' ' + cgst.toFixed(2);
            document.getElementById('sgst_total').innerText = currencySymbol + ' ' + sgst.toFixed(2);
            document.getElementById('igst_total').innerText = currencySymbol + ' ' + igst.toFixed(2);
            document.getElementById('shipping_total').innerText = currencySymbol + ' ' + shipping.toFixed(2);
            document.getElementById('grand_total').innerText = currencySymbol + ' ' + (taxable + cgst + sgst + igst + shipping).toFixed(2);
        }

        function updateTaxFieldStates(companyState, placeOfSupply) {
            var cgstGroup = document.getElementById('cgst_group');
            var sgstGroup = document.getElementById('sgst_group');
            var igstGroup = document.getElementById('igst_group');
            var taxInfoAlert = document.getElementById('tax_info_alert');
            var taxInfoText = document.getElementById('tax_info_text');

            // Reset all classes
            taxInfoAlert.classList.remove('same-state', 'different-state', 'foreign-invoice');
            cgstGroup.classList.remove('disabled', 'hidden');
            sgstGroup.classList.remove('disabled', 'hidden');
            igstGroup.classList.remove('disabled', 'hidden');

            // Check if this is a foreign invoice (foreign party OR non-India region)
            if (isForeignInvoice()) {
                taxInfoAlert.classList.add('foreign-invoice');
                taxInfoText.innerHTML = '<strong>Foreign/Export Invoice</strong> — No GST applies for foreign parties or non-India region.';
                cgstGroup.classList.add('hidden');
                sgstGroup.classList.add('hidden');
                igstGroup.classList.add('hidden');
                document.getElementById('cgst_rate').value = '';
                document.getElementById('sgst_rate').value = '';
                document.getElementById('igst_rate').value = '';
                return;
            }

            if (!companyState) {
                taxInfoText.innerHTML = '<strong>Select a company</strong> to see which taxes will apply';
                return;
            }

            if (!placeOfSupply) {
                taxInfoText.innerHTML = '<strong>Enter Place of Supply</strong> to determine tax type';
                return;
            }

            if (companyState == placeOfSupply) {
                // Same state - CGST + SGST applies, hide IGST
                taxInfoAlert.classList.add('same-state');
                taxInfoText.innerHTML = '<strong>Intra-State Sale (Same State)</strong> — CGST + SGST will apply.';
                igstGroup.classList.add('hidden');
                document.getElementById('igst_rate').value = '';
            } else {
                // Different state - IGST applies, hide CGST & SGST
                taxInfoAlert.classList.add('different-state');
                taxInfoText.innerHTML = '<strong>Inter-State Sale (Different State)</strong> — IGST will apply.';
                cgstGroup.classList.add('hidden');
                sgstGroup.classList.add('hidden');
                document.getElementById('cgst_rate').value = '';
                document.getElementById('sgst_rate').value = '';
            }
        }

        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                var row = e.target.closest('tr');
                row.parentNode.removeChild(row);
                recalcAll();
            }
            // Handle remove button click if clicked on icon
            if (e.target && e.target.closest('.remove-row')) {
                var row = e.target.closest('tr');
                row.parentNode.removeChild(row);
                recalcAll();
            }
        });

        document.getElementById('add_row').addEventListener('click', function () {
            var tbody = document.querySelector('#items_table tbody');
            var index = tbody.querySelectorAll('tr').length;
            var tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
            <td><input name="items[${index}][description_of_goods]" class="input-cell" placeholder="Enter description"></td>
            <td><input name="items[${index}][hsn_code]" class="input-cell" placeholder="HSN"></td>
            <td><input type="number" step="0.01" name="items[${index}][quantity]" class="input-cell quantity" placeholder="0.00"></td>
            <td>
                <select name="items[${index}][unit]" class="form-control unit">
                    <option value="pieces">Pcs</option>
                    <option value="carats">Cts</option>
                </select>
            </td>
            <td><input type="number" step="0.01" name="items[${index}][rate]" class="input-cell rate" placeholder="0.00"></td>
            <td><input type="number" step="0.01" name="items[${index}][amount]" class="input-cell amount" readonly></td>
            <td class="td-action">
                <button type="button" class="btn-remove remove-row" title="Remove Item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
            tbody.appendChild(tr);
        });

        document.querySelector('#items_table').addEventListener('input', function (e) {
            var row = e.target.closest('tr');
            if (e.target.classList.contains('quantity') || e.target.classList.contains('rate')) {
                recalcRow(row);
                recalcAll();
            } else if (e.target.classList.contains('amount')) {
                recalcAll();
            }
        });

        document.getElementById('cgst_rate').addEventListener('input', recalcAll);
        document.getElementById('sgst_rate').addEventListener('input', recalcAll);
        document.getElementById('igst_rate').addEventListener('input', recalcAll);
        document.getElementById('express_shipping').addEventListener('input', recalcAll);
        document.getElementById('place_of_supply').addEventListener('input', recalcAll);

        document.getElementById('company_select').addEventListener('change', function () {
            var id = this.value;
            if (!id) {
                document.getElementById('company_gst').innerText = '—';
                document.getElementById('company_address').innerText = '—';
                document.getElementById('company_bank').innerText = '—';
                return;
            }
            fetch('/admin/companies/' + id)
                .then(function (r) {
                    if (!r.ok) throw new Error('Failed to fetch company');
                    return r.json();
                })
                .then(function (data) {
                    document.getElementById('company_gst').innerText = data.gst_no || '—';
                    document.getElementById('company_gst').dataset.state = data.state_code || '';
                    document.getElementById('company_address').innerText = data.address || '—';
                    document.getElementById('company_bank').innerText = data.bank_name ? (data.bank_name + ' / ' + (data.account_no || '')) : '—';
                    // set place_of_supply default to company state code
                    document.getElementById('place_of_supply').value = data.state_code || '';
                    recalcAll();
                }).catch(function () {
                    console.error('company fetch failed');
                    document.getElementById('company_gst').innerText = '—';
                    document.getElementById('company_address').innerText = '—';
                    document.getElementById('company_bank').innerText = '—';
                    alert('Failed to load company details. Please try again.');
                });
        });        // initial recalc
        setTimeout(recalcAll, 200);

        // Handle region change - update currency symbol and GST visibility
        var regionSelect = document.querySelector('[name="invoice_region"]');
        if (regionSelect) {
            regionSelect.addEventListener('change', function () {
                recalcAll();
            });
        }

        // Handle billed party change - check if foreign party
        var billedSelect = document.getElementById('billed_select');
        if (billedSelect) {
            billedSelect.addEventListener('change', function () {
                var partyId = this.value;
                if (!partyId) {
                    billedPartyIsForeign = false;
                    recalcAll();
                    return;
                }
                // Fetch party details to check is_foreign
                fetch('/admin/parties/' + partyId, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        billedPartyIsForeign = data.is_foreign == 1 || data.is_foreign === true;
                        recalcAll();
                    })
                    .catch(function () {
                        console.error('Party fetch failed');
                        billedPartyIsForeign = false;
                        recalcAll();
                    });
            });

            // Check initial billed party on page load
            if (billedSelect.value) {
                fetch('/admin/parties/' + billedSelect.value, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        billedPartyIsForeign = data.is_foreign == 1 || data.is_foreign === true;
                        recalcAll();
                    })
                    .catch(function () { });
            }
        }
    })();
</script>
