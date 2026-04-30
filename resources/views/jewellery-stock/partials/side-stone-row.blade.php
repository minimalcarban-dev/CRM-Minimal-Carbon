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
                <option value="{{ $st->id }}" {{ (isset($stone) && $stone->stone_type_id == $st->id) || old("side_stones.$index.stone_type_id") == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Shape</label>
            <select name="side_stones[{{ $index }}][stone_shape_id]" class="form-control themed-select">
                <option value="">Select Shape</option>
                @foreach ($stoneShapes as $ss)
                <option value="{{ $ss->id }}" {{ (isset($stone) && $stone->stone_shape_id == $ss->id) || old("side_stones.$index.stone_shape_id") == $ss->id ? 'selected' : '' }}>{{ $ss->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Weight (cts)</label>
            <input type="number" name="side_stones[{{ $index }}][weight]" class="form-control" step="0.001" value="{{ $stone->weight ?? old("side_stones.$index.weight") }}" placeholder="0.000">
        </div>
        <div class="form-group">
            <label class="form-label">Count</label>
            <input type="number" name="side_stones[{{ $index }}][count]" class="form-control" value="{{ $stone->count ?? old("side_stones.$index.count") }}" placeholder="1">
        </div>
        <div class="form-group">
            <label class="form-label">Cut</label>
            <select name="side_stones[{{ $index }}][stone_cut_id]" class="form-control themed-select">
                <option value="">Select Cut</option>
                @foreach ($diamondCuts as $dc)
                <option value="{{ $dc->id }}" {{ (isset($stone) && $stone->stone_cut_id == $dc->id) || old("side_stones.$index.stone_cut_id") == $dc->id ? 'selected' : '' }}>{{ $dc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Color</label>
            <select name="side_stones[{{ $index }}][stone_color_id]" class="form-control themed-select">
                <option value="">Select Color</option>
                @foreach ($stoneColors as $sc)
                <option value="{{ $sc->id }}" {{ (isset($stone) && $stone->stone_color_id == $sc->id) || old("side_stones.$index.stone_color_id") == $sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Clarity</label>
            <select name="side_stones[{{ $index }}][stone_clarity_id]" class="form-control themed-select">
                <option value="">Select Clarity</option>
                @foreach ($diamondClarities as $dcl)
                <option value="{{ $dcl->id }}" {{ (isset($stone) && $stone->stone_clarity_id == $dcl->id) || old("side_stones.$index.stone_clarity_id") == $dcl->id ? 'selected' : '' }}>{{ $dcl->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
