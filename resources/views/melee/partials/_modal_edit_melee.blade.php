    <!-- Edit Melee Diamond Modal -->
    <div class="modal fade" id="editMeleeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-0 text-white"
                    style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:40px; height:40px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Edit Melee Diamond</h5>
                            <small class="opacity-75">Update shape, size & last entry</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editMeleeForm">
                        @csrf
                        <input type="hidden" id="edit_melee_id">

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary text-uppercase small ls-1">Shape</label>
                                <input type="text" id="edit_shape" class="form-control" required
                                    placeholder="e.g. Round brilliant">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary text-uppercase small ls-1">Size</label>
                                <input type="text" id="edit_size" class="form-control" required
                                    placeholder="e.g. 1.5 or 4*2">
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mb-4"
                            style="background: var(--surface-2, #f8f9fa); border: 1px solid var(--border-color, #e9ecef);">
                            <div class="text-secondary small fw-bold text-uppercase mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-down-circle text-success"></i>
                                Latest "IN" Entry
                                <span class="fw-normal text-muted text-lowercase">(leave blank if none)</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-secondary text-uppercase small">Pieces</label>
                                    <input type="number" id="edit_last_pieces" class="form-control"
                                        placeholder="0" min="1">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-secondary text-uppercase small">Carats</label>
                                    <input type="number" step="0.001" id="edit_last_carats" class="form-control"
                                        placeholder="0.000" min="0">
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2"
                            id="btnUpdateMelee">
                            <i class="bi bi-save"></i>
                            <span>Update Details</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
