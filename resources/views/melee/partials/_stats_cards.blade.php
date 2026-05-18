<!-- Stats Cards (visible to Super Admin only) -->
@if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super)
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-gem"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Diamonds</div>
                <div class="stat-value">{{ number_format($allMeleeCount ?? 0) }}</div>
                <div class="stat-breakdown">
                    <span class="breakdown-item breakdown-success" title="In Stock">
                        <i class="bi bi-check-circle"></i> In: {{ number_format($inStockCount ?? 0) }}
                    </span>
                    <span class="breakdown-item text-secondary" title="Out of Stock">
                        <i class="bi bi-dash-circle"></i> Out: {{ number_format($outOfStockCount ?? 0) }}
                    </span>
                    @if (($negativeStockCount ?? 0) > 0)
                        <span class="breakdown-item breakdown-danger" title="Negative Stock">
                            <i class="bi bi-exclamation-triangle"></i> Neg:
                            {{ number_format($negativeStockCount ?? 0) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Value</div>
                <div class="stat-value">${{ number_format($totalValue ?? 0, 2) }}</div>
                <div class="stat-trend">
                    <i class="bi bi-graph-up"></i> Inventory
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-tag"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Avg. Value</div>
                <div class="stat-value">
                    ${{ number_format($avgValue ?? 0, 2) }}
                </div>
                <div class="stat-trend">
                    <i class="bi bi-calculator"></i> Per Parcel
                </div>
            </div>
        </div>
    </div>
@endif