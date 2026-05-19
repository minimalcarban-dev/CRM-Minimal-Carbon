<!-- Header -->
<div class="page-header">
    <div>
        <h2 class="mb-1 fw-bold text-dark"><i class="bi bi-gem me-2 text-primary"></i>Melee Inventory</h2>
        <div class="text-secondary small">Manage your melee diamond stock</div>
    </div>

    <div class="d-flex gap-2">
        <!-- Tab Switcher implemented as Buttons -->
        <button class="btn btn-theme-tab active" id="btn-tab-lab" onclick="switchMainTab('lab-grown')">
            Lab Grown
        </button>
        <button class="btn btn-theme-tab" id="btn-tab-natural" onclick="switchMainTab('natural')">
            Natural
        </button>

        <div class="vr mx-2"></div>

        <button class="btn btn-theme-primary" onclick="openTransactionModal('in')">
            <i class="bi bi-plus-lg me-2"></i>Add Stock
        </button>
        <button class="btn btn-theme-danger-outline" onclick="openTransactionModal('out')">
            <i class="bi bi-dash-lg me-2"></i>Use Stock
        </button>
    </div>
</div>