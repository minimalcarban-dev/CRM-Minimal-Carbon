<!-- Compose Modal -->
<div id="composeModal" class="modal-overlay">
    <div class="modal-content-card">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="bi bi-pencil-square"></i>
                <span id="composeModalTitle">New Message</span>
            </h3>
            <button class="btn-close-modal" id="btnCloseCompose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="composeForm">
            @csrf
            <div class="modal-body">
                <div class="form-group-custom">
                    <label for="to">To</label>
                    <input type="email" id="to" name="to" class="form-control-custom"
                        placeholder="recipient@example.com" required>
                </div>
                <div class="form-group-custom">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control-custom"
                        placeholder="Enter subject">
                </div>
                <div class="form-group-custom body-group">
                    <label for="body">Message</label>
                    <div id="editor-container" class="form-control-custom textarea-custom"
                        style="overflow-y: auto; max-height: 50vh;"
                        onclick="document.getElementById('editor-body').focus()">
                        <div id="editor-body" contenteditable="true" style="min-height: 200px; outline: none;"></div>
                    </div>
                    <style>
                        #editor-body img {
                            max-width: 100%;
                            height: auto;
                            display: block;
                        }
                    </style>
                    <input type="hidden" id="body" name="body" required>
                </div>
            </div>
            <div class="modal-footer">
                <div class="footer-left">
                    <button type="button" class="btn-secondary-custom" id="btnSaveDraft">
                        <i class="bi bi-file-earmark-text"></i>
                        Save Draft
                    </button>
                </div>
                <div class="footer-right">
                    <button type="submit" class="btn-primary-custom" id="btnSend">
                        <i class="bi bi-send"></i>
                        Send Message
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>