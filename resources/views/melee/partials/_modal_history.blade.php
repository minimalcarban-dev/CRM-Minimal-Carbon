    <!-- Stock History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                <div class="modal-header"
                    style="background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; border:0;">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="bi bi-clock-history me-2"></i>Stock History
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Diamond Info Header -->
                    <div id="history-diamond-info"
                        class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <strong id="history-diamond-name">Loading...</strong>
                            <div class="text-muted small" id="history-diamond-detail"></div>
                            <div class="text-muted small fw-semibold" id="history-price-summary"></div>
                        </div>
                        <div>
                            <span id="history-stock-badge"
                                class="badge bg-primary-subtle text-primary px-3 py-2 fs-6 rounded-pill"></span>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div id="history-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading history...</p>
                    </div>
                    <div id="history-empty" class="text-center py-5 hidden">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No transactions recorded yet.</p>
                    </div>
                    <table class="table table-custom mb-0" id="history-table" style="display:none;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>User</th>
                                <th>Pieces</th>
                                <th>Carat</th>
                                <th>Avg $/Ct</th>
                                <th>Total Price</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
