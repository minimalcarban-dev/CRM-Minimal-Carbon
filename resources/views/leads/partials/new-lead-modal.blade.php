{{-- New Lead Modal --}}
<div class="modal fade" id="newLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form action="{{ route('leads.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="border-bottom: 1px solid var(--border); padding: 1.5rem;">
                    <h5 class="modal-title" style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-person-plus" style="color: var(--primary);"></i>
                        Create New Lead
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-grid" style="display: grid; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Customer name">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="+1234567890">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Platform <span class="required">*</span></label>
                                <select name="platform" class="form-control" required>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Platform User ID <span class="required">*</span></label>
                                <input type="text" name="platform_user_id" class="form-control" required
                                    placeholder="User ID from platform">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Any initial notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border); padding: 1rem 1.5rem;">
                    <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-check-circle"></i>
                        Create Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>