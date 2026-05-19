    <!-- Quick Order View Modal -->
    <div class="modal fade" id="quickOrderModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-0 text-white"
                    style="background: linear-gradient(135deg, #1a1a2e, #16213e); padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width:40px; height:40px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-card-checklist fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Order Overview</h5>
                            <small class="opacity-75">Quick reference details</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="quick-order-content">
                    <div class="text-center py-5" id="quick-order-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Fetching order details...</p>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3">
                    <a href="#" id="quick-order-full-link"
                        class="btn btn-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-up-right"></i>
                        View Full Order Details
                    </a>
                </div>
            </div>
        </div>
    </div>
