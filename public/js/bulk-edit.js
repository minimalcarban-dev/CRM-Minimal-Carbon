/**
 * Bulk Edit Manager for Diamond Module
 * Handles multi-select, field editing, and confirmation workflow
 */
class BulkEditManager {
    constructor() {
        this.selectedIds = new Set();
        this.currentStep = 1;
        this.selectedFields = new Set();
        this.fieldValues = {};
        this.allDiamonds = [];
        this.filteredDiamonds = [];
        this.config = window.bulkEditConfig || {};

        // Pagination state
        this.currentPage = 1;
        this.perPage = 50;
        this.totalDiamonds = 0;
        this.hasMore = false;
        this.isLoading = false;

        // Filter state
        this.filters = {
            shape: "",
            status: "",
            admin_id: "",
            search: "",
        };

        // Debounce timer
        this.searchTimer = null;
    }

    /**
     * Initialize the bulk edit functionality
     */
    init() {
        // Bind trigger button
        const trigger = document.getElementById("bulkEditTrigger");
        if (trigger) {
            trigger.addEventListener("click", () => this.openModal());
        }

        // Close on escape key
        document.addEventListener("keydown", (e) => {
            if (
                e.key === "Escape" &&
                !document
                    .getElementById("bulkEditModal")
                    ?.classList.contains("d-none")
            ) {
                this.closeModal();
            }
        });
    }

    /**
     * Open the bulk edit modal
     */
    openModal() {
        const modal = document.getElementById("bulkEditModal");
        if (modal) {
            modal.classList.remove("d-none");
            document.body.style.overflow = "hidden";
            this.resetFilters();
            this.loadDiamonds(true);
        }
    }

    /**
     * Close the bulk edit modal
     */
    closeModal() {
        const modal = document.getElementById("bulkEditModal");
        if (modal) {
            modal.classList.add("d-none");
            document.body.style.overflow = "";
            this.reset();
        }
    }

    /**
     * Reset all state
     */
    reset() {
        this.selectedIds.clear();
        this.selectedFields.clear();
        this.fieldValues = {};
        this.currentStep = 1;
        this.currentPage = 1;
        this.goToStep(1);
        this.updateSelectionCount();

        // Reset all checkboxes including Select All
        const selectAllCheckbox = document.getElementById("selectAllDiamonds");
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }

        // Reset field checkboxes
        document
            .querySelectorAll('input[name="fields[]"]')
            .forEach((cb) => (cb.checked = false));

        // Reset confirmation
        const confirmInput = document.getElementById("confirmInput");
        if (confirmInput) confirmInput.value = "";

        const btnApply = document.getElementById("btnApply");
        if (btnApply) btnApply.disabled = true;

        // Reset next button
        const btnNextStep2 = document.getElementById("btnNextStep2");
        if (btnNextStep2) btnNextStep2.disabled = true;

        // Reset filters
        this.resetFilters();
    }

    /**
     * Reset all filters to default
     */
    resetFilters() {
        this.filters = { shape: "", status: "", admin_id: "", search: "" };
        this.currentPage = 1;

        // Reset filter UI elements
        const filterShape = document.getElementById("filterShape");
        const filterStatus = document.getElementById("filterStatus");
        const filterAdmin = document.getElementById("filterAdmin");
        const searchInput = document.getElementById("bulkEditSearch");

        if (filterShape) filterShape.value = "";
        if (filterStatus) filterStatus.value = "";
        if (filterAdmin) filterAdmin.value = "";
        if (searchInput) searchInput.value = "";
    }

    /**
     * Apply filters from UI
     */
    applyFilters() {
        const filterShape = document.getElementById("filterShape");
        const filterStatus = document.getElementById("filterStatus");
        const filterAdmin = document.getElementById("filterAdmin");

        this.filters.shape = filterShape?.value || "";
        this.filters.status = filterStatus?.value || "";
        this.filters.admin_id = filterAdmin?.value || "";

        this.currentPage = 1;
        this.loadDiamonds(true);
    }

    /**
     * Debounced search
     */
    debounceSearch(query) {
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => {
            this.filters.search = query;
            this.currentPage = 1;
            this.loadDiamonds(true);
        }, 300);
    }

    /**
     * Load diamonds via AJAX with filters and pagination
     */
    async loadDiamonds(reset = false) {
        if (this.isLoading) return;
        this.isLoading = true;

        const listContainer = document.getElementById("diamondList");
        const loadMoreSection = document.getElementById("loadMoreSection");
        const loadMoreBtn = document.getElementById("loadMoreBtn");

        if (reset) {
            this.allDiamonds = [];
            listContainer.innerHTML = `
                <div class="bulk-edit-loading">
                    <div class="spinner"></div>
                    <p>Loading diamonds...</p>
                </div>
            `;
        }

        if (loadMoreBtn) loadMoreBtn.disabled = true;

        try {
            // Build URL with filters and pagination
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
            });

            if (this.filters.shape) params.append("shape", this.filters.shape);
            if (this.filters.status)
                params.append("status", this.filters.status);
            if (this.filters.admin_id)
                params.append("admin_id", this.filters.admin_id);
            if (this.filters.search)
                params.append("search", this.filters.search);

            const url = `${this.config.diamondsUrl}?${params.toString()}`;

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": this.config.csrfToken,
                },
            });

            if (!response.ok) throw new Error("Failed to load diamonds");

            const data = await response.json();

            // Append new diamonds or replace
            if (reset) {
                this.allDiamonds = data.diamonds || [];
            } else {
                this.allDiamonds = [
                    ...this.allDiamonds,
                    ...(data.diamonds || []),
                ];
            }

            this.filteredDiamonds = [...this.allDiamonds];
            this.totalDiamonds = data.total || 0;
            this.hasMore = data.has_more || false;

            this.renderDiamondList();
            document.getElementById("totalDiamondCount").textContent =
                this.totalDiamonds;

            // Show/hide load more button
            if (loadMoreSection) {
                loadMoreSection.style.display = this.hasMore ? "block" : "none";
            }

            const loadMoreCount = document.getElementById("loadMoreCount");
            if (loadMoreCount && this.hasMore) {
                const remaining = this.totalDiamonds - this.allDiamonds.length;
                loadMoreCount.textContent = `(${remaining} more)`;
            }
        } catch (error) {
            console.error("Error loading diamonds:", error);
            listContainer.innerHTML = `
                <div class="bulk-edit-loading">
                    <p style="color: #dc2626;">Failed to load diamonds. Please try again.</p>
                </div>
            `;
        } finally {
            this.isLoading = false;
            if (loadMoreBtn) loadMoreBtn.disabled = false;
        }
    }

    /**
     * Load more diamonds
     */
    loadMore() {
        if (this.hasMore && !this.isLoading) {
            this.currentPage++;
            this.loadDiamonds(false);
        }
    }

    /**
     * Render the diamond list
     */
    renderDiamondList() {
        const listContainer = document.getElementById("diamondList");

        if (this.filteredDiamonds.length === 0) {
            listContainer.innerHTML =
                '<p style="padding: 20px; text-align: center; color: #6b7280;">No diamonds found</p>';
            return;
        }

        listContainer.innerHTML = this.filteredDiamonds
            .map(
                (diamond) => `
            <div class="diamond-item ${
                this.selectedIds.has(diamond.id) ? "selected" : ""
            }" 
                 onclick="BulkEdit.toggleDiamond(${diamond.id})">
                <input type="checkbox" 
                       ${this.selectedIds.has(diamond.id) ? "checked" : ""} 
                       onclick="event.stopPropagation(); BulkEdit.toggleDiamond(${
                           diamond.id
                       })">
                <div class="diamond-item-info">
                    <div class="diamond-item-sku">${diamond.sku || "N/A"}</div>
                    <div class="diamond-item-details">
                        Lot: ${diamond.lot_no || "N/A"} | ${
                    diamond.shape || "N/A"
                } | ${diamond.weight || 0} ct
                    </div>
                </div>
                <div class="diamond-item-price">$${parseFloat(
                    diamond.purchase_price || 0
                ).toFixed(2)}</div>
            </div>
        `
            )
            .join("");
    }

    /**
     * Filter diamonds by search
     */
    filterDiamonds(query) {
        const q = query.toLowerCase();
        this.filteredDiamonds = this.allDiamonds.filter(
            (d) =>
                (d.sku && d.sku.toLowerCase().includes(q)) ||
                (d.lot_no && d.lot_no.toLowerCase().includes(q)) ||
                (d.shape && d.shape.toLowerCase().includes(q))
        );
        this.renderDiamondList();
    }

    /**
     * Toggle diamond selection
     */
    toggleDiamond(id) {
        if (this.selectedIds.has(id)) {
            this.selectedIds.delete(id);
        } else {
            this.selectedIds.add(id);
        }
        this.renderDiamondList();
        this.updateSelectionCount();
    }

    /**
     * Toggle select all
     */
    toggleSelectAll(checked) {
        if (checked) {
            this.filteredDiamonds.forEach((d) => this.selectedIds.add(d.id));
        } else {
            this.filteredDiamonds.forEach((d) => this.selectedIds.delete(d.id));
        }
        this.renderDiamondList();
        this.updateSelectionCount();
    }

    /**
     * Update selection count display
     */
    updateSelectionCount() {
        document.getElementById(
            "selectionCount"
        ).textContent = `${this.selectedIds.size} selected`;
        document.getElementById("confirmDiamondCount").textContent =
            this.selectedIds.size;
    }

    /**
     * Toggle field selection
     */
    toggleField(field, checked) {
        if (checked) {
            this.selectedFields.add(field);
        } else {
            this.selectedFields.delete(field);
            delete this.fieldValues[field];
        }

        // Enable/disable next button
        const btn = document.getElementById("btnNextStep2");
        btn.disabled =
            this.selectedFields.size === 0 || this.selectedIds.size === 0;
    }

    /**
     * Go to a specific step
     */
    goToStep(step) {
        // Hide all steps
        document
            .querySelectorAll(".bulk-edit-step")
            .forEach((s) => s.classList.add("d-none"));

        // Show target step
        const targetStep = document.getElementById(`step${step}`);
        if (targetStep) {
            targetStep.classList.remove("d-none");
        }

        // If going to step 2, sync fields from DOM and render value inputs
        if (step === 2) {
            this.syncFieldsFromDOM();
            this.renderValueInputs();
        }

        // If going to step 3, render confirmation
        if (step === 3) {
            this.renderConfirmation();
        }

        this.currentStep = step;
    }

    /**
     * Sync selected fields from DOM checkboxes
     * This ensures all checked fields are captured even if click events were missed
     */
    syncFieldsFromDOM() {
        const checkboxes = document.querySelectorAll('input[name="fields[]"]');
        checkboxes.forEach((checkbox) => {
            const field = checkbox.value;
            if (checkbox.checked) {
                this.selectedFields.add(field);
            } else {
                this.selectedFields.delete(field);
                delete this.fieldValues[field];
            }
        });
        console.log("Synced fields from DOM:", Array.from(this.selectedFields));
    }

    /**
     * Render value inputs for selected fields
     * Preserves existing values when re-rendering
     */
    renderValueInputs() {
        const container = document.getElementById("valueInputs");
        const inputs = [];

        this.selectedFields.forEach((field) => {
            // Get existing value if any
            const existingValue = this.fieldValues[field] || "";
            inputs.push(this.getFieldInput(field, existingValue));
        });

        container.innerHTML = inputs.join("");
    }

    /**
     * Get HTML input for a field
     * @param {string} field - Field name
     * @param {string} existingValue - Existing value to pre-populate
     */
    getFieldInput(field, existingValue = "") {
        const fieldConfigs = {
            margin: {
                label: "Margin (%)",
                type: "number",
                placeholder: "Enter margin percentage",
                min: 0,
                max: 100,
            },
            shipping_price: {
                label: "Shipping Price (₹)",
                type: "number",
                placeholder: "Enter shipping price in INR",
                min: 0,
            },
            shape: {
                label: "Shape",
                type: "select",
                options: this.config.shapes || [],
            },
            cut: {
                label: "Cut",
                type: "select",
                options: this.config.cuts || [],
            },
            clarity: {
                label: "Clarity",
                type: "select",
                options: this.config.clarities || [],
            },
            color: {
                label: "Color",
                type: "select",
                options: this.config.colors || [],
            },
            material: {
                label: "Material",
                type: "select",
                options: this.config.materials || [],
            },
            diamond_type: {
                label: "Diamond Type",
                type: "select",
                options: this.config.diamondTypes || [],
            },
            admin_id: {
                label: "Assigned To",
                type: "select",
                options: this.config.admins || [],
                optionValue: "id",
                optionLabel: "name",
            },
            is_sold_out: {
                label: "Status",
                type: "select",
                options: [
                    { value: "IN Stock", label: "IN Stock" },
                    { value: "Sold", label: "Sold" },
                ],
                optionValue: "value",
                optionLabel: "label",
            },
            note: {
                label: "Notes",
                type: "textarea",
                placeholder: "Enter notes...",
            },
        };

        const config = fieldConfigs[field] || { label: field, type: "text" };

        if (config.type === "select") {
            const optVal = config.optionValue || null;
            const optLabel = config.optionLabel || null;

            // Convert options to array if it's an object (Laravel sometimes sends objects instead of arrays)
            let optionsArray = config.options || [];
            if (!Array.isArray(optionsArray)) {
                optionsArray = Object.values(optionsArray);
            }

            const options = optionsArray
                .map((opt) => {
                    const optionValue =
                        typeof opt === "object"
                            ? opt[optVal] || opt.value || opt.id || opt.name
                            : opt;
                    const optionLabel =
                        typeof opt === "object"
                            ? opt[optLabel] ||
                              opt.label ||
                              opt.name ||
                              opt.value
                            : opt;
                    const selected =
                        String(optionValue) === String(existingValue)
                            ? "selected"
                            : "";
                    return `<option value="${optionValue}" ${selected}>${optionLabel}</option>`;
                })
                .join("");

            return `
                <div class="value-input-group">
                    <label>${config.label}</label>
                    <select onchange="BulkEdit.setFieldValue('${field}', this.value)">
                        <option value="">Select ${config.label}...</option>
                        ${options}
                    </select>
                </div>
            `;
        }

        if (config.type === "textarea") {
            return `
                <div class="value-input-group">
                    <label>${config.label}</label>
                    <textarea onchange="BulkEdit.setFieldValue('${field}', this.value)" 
                              oninput="BulkEdit.setFieldValue('${field}', this.value)"
                              placeholder="${
                                  config.placeholder || ""
                              }">${existingValue}</textarea>
                </div>
            `;
        }

        return `
            <div class="value-input-group">
                <label>${config.label}</label>
                <input type="${config.type}" 
                       value="${existingValue}"
                       onchange="BulkEdit.setFieldValue('${field}', this.value)"
                       oninput="BulkEdit.setFieldValue('${field}', this.value)"
                       placeholder="${config.placeholder || ""}"
                       ${config.min !== undefined ? `min="${config.min}"` : ""}
                       ${config.max !== undefined ? `max="${config.max}"` : ""}>
            </div>
        `;
    }

    /**
     * Set field value
     */
    setFieldValue(field, value) {
        this.fieldValues[field] = value;
    }

    /**
     * Render confirmation summary
     */
    renderConfirmation() {
        const fieldsList = document.getElementById("confirmFieldsList");
        const fieldLabels = {
            margin: "Margin",
            shipping_price: "Shipping Price",
            shape: "Shape",
            cut: "Cut",
            clarity: "Clarity",
            color: "Color",
            material: "Material",
            diamond_type: "Diamond Type",
            admin_id: "Assigned To",
            is_sold_out: "Status",
            note: "Notes",
        };

        const items = [];
        this.selectedFields.forEach((field) => {
            const label = fieldLabels[field] || field;
            const value = this.fieldValues[field] || "(not set)";
            items.push(`<li><strong>${label}:</strong> ${value}</li>`);
        });

        fieldsList.innerHTML = items.join("");
    }

    /**
     * Validate confirmation input
     */
    validateConfirmation(value) {
        const btn = document.getElementById("btnApply");
        btn.disabled = value !== "CONFIRM";
    }

    /**
     * Submit bulk edit
     */
    async submitBulkEdit() {
        const btn = document.getElementById("btnApply");
        btn.disabled = true;
        btn.innerHTML =
            '<span class="spinner" style="width: 20px; height: 20px;"></span> Processing...';

        try {
            const response = await fetch(this.config.apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": this.config.csrfToken,
                },
                body: JSON.stringify({
                    diamond_ids: Array.from(this.selectedIds),
                    fields: Array.from(this.selectedFields),
                    values: this.fieldValues,
                    confirmation: "CONFIRM",
                }),
            });

            const data = await response.json();

            if (data.success) {
                this.closeModal();
                // Show success message
                this.showToast(
                    `Successfully updated ${data.count} diamonds!`,
                    "success"
                );
                // Reload page after short delay
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message || "Failed to update diamonds");
            }
        } catch (error) {
            console.error("Bulk edit error:", error);
            this.showToast(
                error.message || "Failed to update diamonds. Please try again.",
                "error"
            );
            btn.disabled = false;
            btn.innerHTML = "Apply Changes";
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = "success") {
        // Use existing toast system if available, otherwise use alert
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }
}

// Initialize on DOM ready
const BulkEdit = new BulkEditManager();
document.addEventListener("DOMContentLoaded", () => BulkEdit.init());
