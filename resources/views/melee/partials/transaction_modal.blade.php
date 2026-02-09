<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 bg-light pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Quick Stock Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="transactionForm">
                    @csrf
                    <input type="hidden" name="melee_diamond_id" id="modal_diamond_id">

                    <!-- Selected Item Context (Dynamic) -->
                    <div id="selection_context" class="bg-white border rounded p-3 mb-4 d-flex align-items-center gap-3"
                        style="display:none;">
                        <div class="bg-light p-2 rounded text-primary">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-secondary fs-8 fw-bold ls-1 mb-1">Selected Item</div>
                            <div class="fw-bold text-dark" id="modal_item_name">Unknown Item</div>
                            <div class="text-muted small" id="modal_item_cat">Category</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-link ms-auto text-decoration-none"
                            onclick="resetModalSelection()">Change</button>
                    </div>

                    <!-- Diamond Selector (Visible when no item selected) -->
                    <div id="diamond_selector_container" class="mb-4">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Select Melee
                            Diamond</label>
                        <select id="modal_diamond_select" class="form-control" style="width: 100%;">
                            <option value="">-- Search Melee Diamond (Shape, Size, etc.) --</option>
                        </select>
                    </div>

                    <!-- Transaction Type Toggle -->
                    <div class="d-flex justify-content-center mb-4 bg-light rounded-pill p-1 mx-5">
                        <input type="radio" class="btn-check" name="transaction_type" id="type_in" value="in" checked
                            onchange="updateModalTheme('in')">
                        <label class="btn btn-sm rounded-pill w-50 fw-bold transition-all text-uppercase" for="type_in"
                            id="lbl_in">
                            <i class="bi bi-plus-lg me-1"></i> Add (IN)
                        </label>

                        <input type="radio" class="btn-check" name="transaction_type" id="type_out" value="out"
                            onchange="updateModalTheme('out')">
                        <label class="btn btn-sm rounded-pill w-50 fw-bold transition-all text-uppercase" for="type_out"
                            id="lbl_out">
                            <i class="bi bi-dash-lg me-1"></i> Use (OUT)
                        </label>
                    </div>

                    <!-- Inputs -->
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Pieces</label>
                            <input type="number" name="pieces" class="form-control form-control-lg fw-bold"
                                placeholder="0" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Carats</label>
                            <input type="number" name="carat_weight" step="0.001" class="form-control form-control-lg"
                                placeholder="0.000" min="0" required>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Reference /
                            Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="mt-4">
                        <button type="submit"
                            class="btn w-100 py-2 fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2"
                            id="submitBtn">
                            <span>Confirm Transaction</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateModalTheme(type) {
        const btnSubmit = document.getElementById('submitBtn');
        const lblIn = document.getElementById('lbl_in');
        const lblOut = document.getElementById('lbl_out');

        if (type === 'in') {
            lblIn.classList.add('btn-success', 'text-white');
            lblIn.classList.remove('text-secondary');

            lblOut.classList.remove('btn-danger', 'text-white');
            lblOut.classList.add('text-secondary');

            btnSubmit.className = 'btn w-100 py-2 fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2 btn-success';
        } else {
            lblIn.classList.remove('btn-success', 'text-white');
            lblIn.classList.add('text-secondary');

            lblOut.classList.add('btn-danger', 'text-white');
            lblOut.classList.remove('text-secondary');

            btnSubmit.className = 'btn w-100 py-2 fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2 btn-danger';
        }
    }

    // Initialize default theme
    updateModalTheme('in');

    // Form Submission
    document.getElementById('transactionForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const btn = document.getElementById('submitBtn');
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch("{{ route('melee.transaction') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success: ' + data.message);
                    location.reload(); // Simple reload for now to update UI
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });
</script>