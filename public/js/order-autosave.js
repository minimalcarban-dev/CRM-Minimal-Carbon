/**
 * Order Auto-Save System
 * Automatically saves order form data to prevent data loss
 */

(function () {
    "use strict";

    // Configuration
    const CONFIG = {
        autoSaveInterval: 30000, // 30 seconds
        localStorageKey: "order_draft_",
        saveEndpoint: "/admin/orders/drafts/save",
        debounceDelay: 2000, // 2 seconds after last change
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
    };

    // State
    let autoSaveTimer = null;
    let debounceTimer = null;
    let currentDraftId = null;
    let lastSavedData = null;
    let isInitialized = false;

    /**
     * Initialize the auto-save system
     */
    function init() {
        if (isInitialized) return;

        // Check if we're on the order create page
        const orderForm = document.getElementById("orderForm");
        if (!orderForm) return;

        isInitialized = true;

        // Get draft ID if resuming
        const draftInput = document.querySelector('input[name="draft_id"]');
        if (draftInput && draftInput.value) {
            currentDraftId = parseInt(draftInput.value);
        }

        // Set up event listeners
        setupEventListeners();

        // Start auto-save timer
        startAutoSaveTimer();

        // Check for existing local draft
        checkLocalDraft();

        // Show auto-save indicator
        createAutoSaveIndicator();

        console.log("[AutoSave] Initialized");
    }

    /**
     * Set up form event listeners
     */
    function setupEventListeners() {
        const form = document.getElementById("orderForm");
        if (!form) return;

        // Listen for input changes
        form.addEventListener("input", handleFormChange);
        form.addEventListener("change", handleFormChange);

        // Listen for form submission
        form.addEventListener("submit", handleFormSubmit);

        // Listen for page unload
        window.addEventListener("beforeunload", handleBeforeUnload);

        // Listen for order type changes
        document
            .querySelectorAll('input[name="order_type"]')
            .forEach((radio) => {
                radio.addEventListener("change", () => {
                    // Wait for form to load then attach listeners
                    setTimeout(() => {
                        attachDynamicFieldListeners();
                    }, 1000);
                });
            });
    }

    /**
     * Attach listeners to dynamically loaded form fields
     */
    function attachDynamicFieldListeners() {
        const formContainer = document.getElementById("formContainer");
        if (!formContainer) return;

        formContainer.addEventListener("input", handleFormChange);
        formContainer.addEventListener("change", handleFormChange);
    }

    /**
     * Handle form input changes
     */
    function handleFormChange(e) {
        // Debounce the save
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            saveToLocalStorage();
            saveToServer();
        }, CONFIG.debounceDelay);

        updateIndicator("unsaved");
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        // Clear local storage on successful submit attempt
        // The server will handle clearing if successful
        clearLocalDraft();
    }

    /**
     * Handle page unload
     */
    function handleBeforeUnload(e) {
        // Save to local storage before leaving
        saveToLocalStorage();

        // Check if there are unsaved changes
        const currentData = collectFormData();
        if (hasChanges(currentData)) {
            e.preventDefault();
            e.returnValue =
                "You have unsaved changes. Are you sure you want to leave?";
            return e.returnValue;
        }
    }

    /**
     * Collect all form data
     */
    function collectFormData() {
        const form = document.getElementById("orderForm");
        if (!form) return {};

        const formData = new FormData(form);
        const data = {};

        // Exclude file inputs
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) continue;

            // Handle array inputs (like checkboxes with same name)
            if (key.endsWith("[]")) {
                const cleanKey = key.slice(0, -2);
                if (!data[cleanKey]) data[cleanKey] = [];
                data[cleanKey].push(value);
            } else {
                data[key] = value;
            }
        }

        return data;
    }

    /**
     * Check if form data has changed since last save
     */
    function hasChanges(currentData) {
        if (!lastSavedData) return Object.keys(currentData).length > 0;
        return JSON.stringify(currentData) !== JSON.stringify(lastSavedData);
    }

    /**
     * Save to local storage
     */
    function saveToLocalStorage() {
        const orderType = getSelectedOrderType();
        if (!orderType) return;

        const data = collectFormData();
        if (Object.keys(data).length === 0) return;

        const storageKey = CONFIG.localStorageKey + orderType;
        const storageData = {
            data: data,
            timestamp: Date.now(),
            draftId: currentDraftId,
        };

        try {
            localStorage.setItem(storageKey, JSON.stringify(storageData));
            console.log("[AutoSave] Saved to local storage");
        } catch (e) {
            console.error("[AutoSave] Local storage error:", e);
        }
    }

    /**
     * Save to server
     */
    async function saveToServer() {
        const orderType = getSelectedOrderType();
        const data = collectFormData();

        if (Object.keys(data).length === 0) return;
        if (!hasChanges(data)) return;

        updateIndicator("saving");

        try {
            const response = await fetch(CONFIG.saveEndpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CONFIG.csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    order_type: orderType,
                    form_data: data,
                    draft_id: currentDraftId,
                    last_step: getCurrentStep(),
                }),
            });

            const result = await response.json();

            if (result.success) {
                currentDraftId = result.draft_id;
                lastSavedData = data;
                updateIndicator("saved", result.saved_at);
                console.log(
                    "[AutoSave] Saved to server, draft ID:",
                    currentDraftId
                );
            } else {
                updateIndicator("error");
                console.error("[AutoSave] Server error:", result.message);
            }
        } catch (error) {
            updateIndicator("error");
            console.error("[AutoSave] Network error:", error);
        }
    }

    /**
     * Get the currently selected order type
     */
    function getSelectedOrderType() {
        const selected = document.querySelector(
            'input[name="order_type"]:checked'
        );
        return selected ? selected.value : null;
    }

    /**
     * Get the current form step (for multi-step tracking)
     */
    function getCurrentStep() {
        // You can customize this based on your form structure
        const formContainer = document.getElementById("formContainer");
        if (!formContainer || !formContainer.innerHTML.trim()) {
            return "order_type_selection";
        }
        return "form_filling";
    }

    /**
     * Start the auto-save timer
     */
    function startAutoSaveTimer() {
        if (autoSaveTimer) clearInterval(autoSaveTimer);

        autoSaveTimer = setInterval(() => {
            const data = collectFormData();
            if (hasChanges(data)) {
                saveToLocalStorage();
                saveToServer();
            }
        }, CONFIG.autoSaveInterval);
    }

    /**
     * Check for existing local draft
     */
    function checkLocalDraft() {
        // If already resuming a draft, don't check local storage
        if (currentDraftId) return;

        const orderType = getSelectedOrderType();
        if (!orderType) return;

        const storageKey = CONFIG.localStorageKey + orderType;
        const stored = localStorage.getItem(storageKey);

        if (stored) {
            try {
                const parsed = JSON.parse(stored);
                const hoursSinceLastSave =
                    (Date.now() - parsed.timestamp) / (1000 * 60 * 60);

                // Only restore if less than 24 hours old
                if (
                    hoursSinceLastSave < 24 &&
                    Object.keys(parsed.data).length > 0
                ) {
                    showRestorePrompt(parsed);
                }
            } catch (e) {
                console.error("[AutoSave] Error parsing local draft:", e);
            }
        }
    }

    /**
     * Show prompt to restore local draft
     */
    function showRestorePrompt(draft) {
        const lastSaved = new Date(draft.timestamp).toLocaleString();

        const modal = document.createElement("div");
        modal.className = "autosave-restore-modal";
        modal.innerHTML = `
            <div class="autosave-restore-content">
                <div class="autosave-restore-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <h3>Unsaved Draft Found</h3>
                <p>You have an unsaved draft from <strong>${lastSaved}</strong></p>
                <p class="autosave-restore-hint">Would you like to restore it?</p>
                <div class="autosave-restore-actions">
                    <button type="button" class="btn-restore-discard">Discard</button>
                    <button type="button" class="btn-restore-load">Restore Draft</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Handle discard
        modal
            .querySelector(".btn-restore-discard")
            .addEventListener("click", () => {
                clearLocalDraft();
                modal.remove();
            });

        // Handle restore
        modal
            .querySelector(".btn-restore-load")
            .addEventListener("click", () => {
                restoreFormData(draft.data);
                if (draft.draftId) {
                    currentDraftId = draft.draftId;
                }
                modal.remove();
            });
    }

    /**
     * Restore form data from saved draft
     */
    function restoreFormData(data) {
        // Wait a bit for any dynamic content to load
        setTimeout(() => {
            for (const [key, value] of Object.entries(data)) {
                const field = document.querySelector(`[name="${key}"]`);
                if (!field) continue;

                if (field.type === "checkbox") {
                    field.checked =
                        value === "on" || value === "1" || value === true;
                } else if (field.type === "radio") {
                    const radio = document.querySelector(
                        `[name="${key}"][value="${value}"]`
                    );
                    if (radio) radio.checked = true;
                } else if (field.tagName === "SELECT") {
                    field.value = value;
                } else {
                    field.value = value;
                }
            }

            lastSavedData = data;
            updateIndicator("saved");
            console.log("[AutoSave] Form data restored");
        }, 500);
    }

    /**
     * Clear local draft
     */
    function clearLocalDraft() {
        const orderTypes = [
            "ready_to_ship",
            "custom_diamond",
            "custom_jewellery",
        ];
        orderTypes.forEach((type) => {
            localStorage.removeItem(CONFIG.localStorageKey + type);
        });
        console.log("[AutoSave] Local drafts cleared");
    }

    /**
     * Create auto-save indicator
     */
    function createAutoSaveIndicator() {
        const indicator = document.createElement("div");
        indicator.id = "autosave-indicator";
        indicator.className = "autosave-indicator";
        indicator.innerHTML = `
            <span class="autosave-icon"></span>
            <span class="autosave-text">Auto-save enabled</span>
        `;

        // Insert after page header or at top of form
        const header =
            document.querySelector(".page-header") ||
            document.getElementById("orderForm");
        if (header) {
            header.parentNode.insertBefore(indicator, header.nextSibling);
        }
    }

    /**
     * Update auto-save indicator
     */
    function updateIndicator(status, time) {
        const indicator = document.getElementById("autosave-indicator");
        if (!indicator) return;

        indicator.className = "autosave-indicator " + status;

        const textEl = indicator.querySelector(".autosave-text");
        switch (status) {
            case "saving":
                textEl.textContent = "Saving...";
                break;
            case "saved":
                textEl.textContent = time
                    ? `Saved at ${time}`
                    : "All changes saved";
                break;
            case "error":
                textEl.textContent = "Save failed - will retry";
                break;
            case "unsaved":
                textEl.textContent = "Unsaved changes";
                break;
            default:
                textEl.textContent = "Auto-save enabled";
        }
    }

    /**
     * Public API
     */
    window.OrderAutoSave = {
        init: init,
        save: saveToServer,
        clearDraft: clearLocalDraft,
        getDraftId: () => currentDraftId,
    };

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
