<div class="form-section-card" style="margin-bottom:1.5rem;">
    <div class="section-header">
        <div class="section-info">
            <div class="section-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="bi bi-diamond"></i></div>
            <div>
                <h3 class="section-title">Secondary / Side Stones</h3>
                <p class="section-description">Add multiple types of side stones with detailed specifications</p>
            </div>
        </div>
        <button type="button" class="btn-primary-custom btn-sm" onclick="addSideStoneRow()">
            <i class="bi bi-plus-lg"></i> Add Stone
        </button>
    </div>
    <div class="section-body">
        <div id="side_stones_container">
            @php
                $oldSideStones = old('side_stones', $jewelleryStock->sideStones ?? []);
            @endphp
            
            @forelse($oldSideStones as $index => $stone)
                @include('jewellery-stock.partials.side-stone-row', ['index' => $index, 'stone' => $stone])
            @empty
                {{-- No stones by default --}}
                <div id="side_stones_empty" class="text-center py-4 text-muted border rounded" style="border-style: dashed !important;">
                    <i class="bi bi-info-circle me-1"></i> No side stones added. Click "Add Stone" to include them.
                </div>
            @endforelse
        </div>
    </div>
</div>

<template id="side_stone_row_template">
    @include('jewellery-stock.partials.side-stone-row', ['index' => 'INDEX_PLACEHOLDER', 'stone' => null])
</template>

@push('scripts')
<script>
    let sideStoneIndex = {{ count($oldSideStones) }};

    function addSideStoneRow() {
        const container = document.getElementById('side_stones_container');
        const emptyState = document.getElementById('side_stones_empty');
        if (emptyState) emptyState.remove();

        const template = document.getElementById('side_stone_row_template').innerHTML;
        const html = template.replace(/INDEX_PLACEHOLDER/g, sideStoneIndex);
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        container.appendChild(wrapper.firstElementChild);
        
        sideStoneIndex++;
    }

    function removeSideStoneRow(btn) {
        btn.closest('.side-stone-row').remove();
        const container = document.getElementById('side_stones_container');
        if (container.children.length === 0) {
            container.innerHTML = `
                <div id="side_stones_empty" class="text-center py-4 text-muted border rounded" style="border-style: dashed !important;">
                    <i class="bi bi-info-circle me-1"></i> No side stones added. Click "Add Stone" to include them.
                </div>
            `;
        }
    }
</script>
@endpush
