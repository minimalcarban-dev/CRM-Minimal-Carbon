@php
    $currentStoneTypeId = old("side_stones.$index.stone_type_id", data_get($stone, 'stone_type_id'));
    $currentShapeId = old("side_stones.$index.stone_shape_id", data_get($stone, 'stone_shape_id'));
    $currentWeight = old("side_stones.$index.weight", data_get($stone, 'weight'));
    $currentCount = old("side_stones.$index.count", data_get($stone, 'count'));
    $currentCutId = old("side_stones.$index.stone_cut_id", data_get($stone, 'stone_cut_id'));
    $currentColorId = old("side_stones.$index.stone_color_id", data_get($stone, 'stone_color_id'));
    $currentClarityId = old("side_stones.$index.stone_clarity_id", data_get($stone, 'stone_clarity_id'));
@endphp

<div class="side-stone-row mb-3 p-3 border rounded bg-light position-relative">
    <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:10px;right:10px;" onclick="removeSideStoneRow(this)">
        <i class="bi bi-trash"></i>
    </button>
    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:1rem;">
        <div class="form-group">
            <label class="form-label">Stone Type <span class="required">*</span></label>
            <select name="side_stones[{{ $index }}][stone_type_id]" class="form-control themed-select" required>
                <option value="">Select Type</option>
                @foreach ($stoneTypes as $st)
                <option value="{{ $st->id }}" {{ $currentStoneTypeId == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Shape</label>
            <select name="side_stones[{{ $index }}][stone_shape_id]" class="form-control themed-select">
                <option value="">Select Shape</option>
                @foreach ($stoneShapes as $ss)
                <option value="{{ $ss->id }}" {{ $currentShapeId == $ss->id ? 'selected' : '' }}>{{ $ss->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Weight (cts)</label>
            <input type="number" name="side_stones[{{ $index }}][weight]" class="form-control" step="0.001" value="{{ $currentWeight }}" placeholder="0.000">
        </div>
        <div class="form-group">
            <label class="form-label">Count</label>
            <input type="number" name="side_stones[{{ $index }}][count]" class="form-control" value="{{ $currentCount }}" placeholder="1">
        </div>
        <div class="form-group">
            <label class="form-label">Cut</label>
            <select name="side_stones[{{ $index }}][stone_cut_id]" class="form-control themed-select">
                <option value="">Select Cut</option>
                @foreach ($diamondCuts as $dc)
                <option value="{{ $dc->id }}" {{ $currentCutId == $dc->id ? 'selected' : '' }}>{{ $dc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Color</label>
            <select name="side_stones[{{ $index }}][stone_color_id]" class="form-control themed-select">
                <option value="">Select Color</option>
                @foreach ($stoneColors as $sc)
                <option value="{{ $sc->id }}" {{ $currentColorId == $sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Clarity</label>
            <select name="side_stones[{{ $index }}][stone_clarity_id]" class="form-control themed-select">
                <option value="">Select Clarity</option>
                @foreach ($diamondClarities as $dcl)
                <option value="{{ $dcl->id }}" {{ $currentClarityId == $dcl->id ? 'selected' : '' }}>{{ $dcl->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
