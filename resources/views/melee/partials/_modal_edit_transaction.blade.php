    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-0 text-white"
                    style="background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:36px; height:36px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-receipt fs-6"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Edit Transaction</h5>
                            <small class="opacity-75">Adjust pieces & carats</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editTransactionForm">
                        @csrf
                        <input type="hidden" id="edit_tx_id">

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary text-uppercase small">Pieces</label>
                            <input type="number" id="edit_tx_pieces" class="form-control form-control-lg"
                                required min="1" placeholder="0">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary text-uppercase small">Carats</label>
                            <input type="number" step="0.001" id="edit_tx_carats" class="form-control form-control-lg"
                                min="0" placeholder="0.000">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btnUpdateTransaction">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
