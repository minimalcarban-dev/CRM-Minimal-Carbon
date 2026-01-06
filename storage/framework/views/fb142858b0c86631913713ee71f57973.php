
<div id="bulkEditModal" class="bulk-edit-modal d-none">
    <div class="bulk-edit-backdrop" onclick="BulkEdit.closeModal()"></div>
    <div class="bulk-edit-container">
        
        <div class="bulk-edit-header">
            <div class="bulk-edit-header-left">
                <h2><i class="bi bi-pencil-square"></i> Bulk Edit Diamonds</h2>
                <p class="bulk-edit-subtitle">Select diamonds and fields to edit</p>
            </div>
            <button type="button" class="bulk-edit-close" onclick="BulkEdit.closeModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        
        <div class="bulk-edit-body">
            
            <div class="bulk-edit-left">
                
                <div class="bulk-edit-filters">
                    <div class="filter-row">
                        <select id="filterShape" onchange="BulkEdit.applyFilters()">
                            <option value="">All Shapes</option>
                            <?php $__currentLoopData = $shapes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shape): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($shape); ?>"><?php echo e($shape); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <select id="filterStatus" onchange="BulkEdit.applyFilters()">
                            <option value="">All Status</option>
                            <option value="IN Stock">In Stock</option>
                            <option value="Sold">Sold</option>
                        </select>
                    </div>
                    <div class="filter-row">
                        <select id="filterAdmin" onchange="BulkEdit.applyFilters()">
                            <option value="">All Admins</option>
                            <?php $__currentLoopData = $admins ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($admin->id); ?>"><?php echo e($admin->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input type="text" id="bulkEditSearch" placeholder="Search SKU/Lot..." 
                            oninput="BulkEdit.debounceSearch(this.value)">
                    </div>
                </div>

                <div class="bulk-edit-select-all">
                    <label>
                        <input type="checkbox" id="selectAllDiamonds" onchange="BulkEdit.toggleSelectAll(this.checked)">
                        <span>Select All (<span id="totalDiamondCount">0</span>)</span>
                    </label>
                    <span class="bulk-edit-selection-count" id="selectionCount">0 selected</span>
                </div>

                <div class="bulk-edit-diamond-list" id="diamondList">
                    
                    <div class="bulk-edit-loading">
                        <div class="spinner"></div>
                        <p>Loading diamonds...</p>
                    </div>
                </div>

                
                <div class="bulk-edit-load-more" id="loadMoreSection" style="display: none;">
                    <button type="button" id="loadMoreBtn" onclick="BulkEdit.loadMore()">
                        Load More <span id="loadMoreCount"></span>
                    </button>
                </div>
            </div>

            
            <div class="bulk-edit-right">
                
                <div class="bulk-edit-step" id="step1">
                    <h3>Select Fields to Edit</h3>
                    <p class="step-description">Choose which fields you want to update for selected diamonds</p>

                    <div class="field-categories">
                        
                        <div class="field-category">
                            <h4><i class="bi bi-currency-dollar"></i> Pricing</h4>
                            <div class="field-options">
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="margin"
                                        onclick="BulkEdit.toggleField('margin', this.checked)">
                                    <span>Margin (%)</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="shipping_price"
                                        onclick="BulkEdit.toggleField('shipping_price', this.checked)">
                                    <span>Shipping Price</span>
                                </label>
                            </div>
                        </div>

                        
                        <div class="field-category">
                            <h4><i class="bi bi-gem"></i> Attributes</h4>
                            <div class="field-options">
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="shape"
                                        onclick="BulkEdit.toggleField('shape', this.checked)">
                                    <span>Shape</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="cut"
                                        onclick="BulkEdit.toggleField('cut', this.checked)">
                                    <span>Cut</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="clarity"
                                        onclick="BulkEdit.toggleField('clarity', this.checked)">
                                    <span>Clarity</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="color"
                                        onclick="BulkEdit.toggleField('color', this.checked)">
                                    <span>Color</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="material"
                                        onclick="BulkEdit.toggleField('material', this.checked)">
                                    <span>Material</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="diamond_type"
                                        onclick="BulkEdit.toggleField('diamond_type', this.checked)">
                                    <span>Diamond Type</span>
                                </label>
                            </div>
                        </div>

                        
                        <div class="field-category">
                            <h4><i class="bi bi-person"></i> Assignment & Status</h4>
                            <div class="field-options">
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="admin_id"
                                        onclick="BulkEdit.toggleField('admin_id', this.checked)">
                                    <span>Assigned To</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="is_sold_out"
                                        onclick="BulkEdit.toggleField('is_sold_out', this.checked)">
                                    <span>Status</span>
                                    <span class="field-warning">⚠️</span>
                                </label>
                                <label class="field-option">
                                    <input type="checkbox" name="fields[]" value="note"
                                        onclick="BulkEdit.toggleField('note', this.checked)">
                                    <span>Notes</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-next" onclick="BulkEdit.goToStep(2)" id="btnNextStep2" disabled>
                        Next: Set Values <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                
                <div class="bulk-edit-step d-none" id="step2">
                    <h3>Set New Values</h3>
                    <p class="step-description">Enter the new values for selected fields</p>

                    <div class="value-inputs" id="valueInputs">
                        
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn-back" onclick="BulkEdit.goToStep(1)">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn-next" onclick="BulkEdit.goToStep(3)" id="btnNextStep3">
                            Review Changes <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

                
                <div class="bulk-edit-step d-none" id="step3">
                    <h3>⚠️ Confirm Bulk Edit</h3>

                    <div class="confirmation-summary">
                        <div class="summary-item">
                            <strong>Diamonds to update:</strong>
                            <span id="confirmDiamondCount">0</span>
                        </div>
                        <div class="summary-item">
                            <strong>Fields to change:</strong>
                            <ul id="confirmFieldsList"></ul>
                        </div>
                    </div>

                    <div class="confirmation-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>This action will permanently update all selected diamonds. This cannot be undone.</p>
                    </div>

                    <div class="confirmation-input">
                        <label>Type <strong>CONFIRM</strong> to proceed:</label>
                        <input type="text" id="confirmInput" oninput="BulkEdit.validateConfirmation(this.value)"
                            placeholder="Type CONFIRM here">
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn-back" onclick="BulkEdit.goToStep(2)">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn-danger" onclick="BulkEdit.submitBulkEdit()" id="btnApply"
                            disabled>
                            Apply Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    window.bulkEditConfig = {
        csrfToken: '<?php echo e(csrf_token()); ?>',
        apiUrl: '<?php echo e(route("diamond.bulk-edit")); ?>',
        diamondsUrl: '<?php echo e(route("diamond.bulk-edit.diamonds")); ?>',
        shapes: <?php echo json_encode($shapes ?? [], 15, 512) ?>,
        cuts: <?php echo json_encode($cuts ?? [], 15, 512) ?>,
        clarities: <?php echo json_encode($clarities ?? [], 15, 512) ?>,
        colors: <?php echo json_encode($colors ?? [], 15, 512) ?>,
        materials: <?php echo json_encode($materials ?? [], 15, 512) ?>,
        diamondTypes: <?php echo json_encode($diamondTypes ?? [], 15, 512) ?>,
        admins: <?php echo json_encode($admins->map(fn($a) => ['id' => $a->id, 'name' => $a->name]) ?? [], 512) ?>
    };
</script><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/diamonds/partials/_bulk-edit-modal.blade.php ENDPATH**/ ?>