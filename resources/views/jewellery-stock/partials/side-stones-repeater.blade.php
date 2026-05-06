<div>
    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <p
            style="font-size:0.8rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin:0;">
            <i class="bi bi-circle-fill" style="font-size:.5rem;vertical-align:middle;margin-right:.4rem;"></i>Secondary /
            Side Stones
        </p>
        <button type="button" class="btn-primary-custom btn-sm" onclick="addSideStoneRow()"
            style="padding: 2px 12px; font-size: 0.75rem; border-radius: 6px;">
            <i class="bi bi-plus-lg"></i> Add Stone
        </button>
    </div>

    <div id="side_stones_container">
        @php
            $oldSideStones = old('side_stones', $jewelleryStock->sideStones ?? []);
        @endphp

        @forelse($oldSideStones as $index => $stone)
            @include('jewellery-stock.partials.side-stone-row', ['index' => $index, 'stone' => $stone])
        @empty
            <div id="side_stones_empty" class="text-center py-4 text-muted border rounded"
                style="border-style: dashed !important; background: rgba(0,0,0,0.02);">
                <i class="bi bi-info-circle me-1"></i> No side stones added.
            </div>
        @endforelse
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
