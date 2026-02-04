<div class="card mb-4 border border-secondary border-opacity-25 bg-dark text-white">
    <div
        class="card-header bg-transparent border-bottom border-secondary border-opacity-25 d-flex align-items-center gap-2">
        <i class="ti ti-diamond fs-4 text-info"></i>
        <h5 class="mb-0 text-white">Meele Diamond Selection</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- Parcel Selector -->
            <div class="col-md-12">
                <label class="form-label text-light">Select Parcel</label>
                <select name="meele_diamond_id" id="meele_diamond_id"
                    class="form-select bg-dark text-white border-secondary">
                    <option value="">Select a Meele Parcel...</option>
                    @foreach(\App\Models\MeeleParcel::where('status', 'active')->get() as $parcel)
                        <option value="{{ $parcel->id }}" data-weight="{{ $parcel->current_weight }}"
                            data-pieces="{{ $parcel->current_pieces }}" data-price="{{ $parcel->avg_cost_per_carat }}">
                            {{ $parcel->parcel_code }} | {{ $parcel->sieve_size }} ({{ $parcel->category }}) - Available:
                            {{ $parcel->current_weight }}ct / {{ $parcel->current_pieces }}pcs
                        </option>
                    @endforeach
                </select>
                <div id="meele_parcel_info" class="form-text text-info mt-1 d-none">
                    <i class="ti ti-info-circle"></i> Available Stock: <span id="meele_avail_weight">0</span> ct / <span
                        id="meele_avail_pieces">0</span> pcs
                </div>
            </div>

            <!-- Input Fields -->
            <div class="col-md-4">
                <label class="form-label text-light">Meele Weight (ct)</label>
                <input type="number" step="0.0001" name="meele_carat" id="meele_carat"
                    class="form-control bg-dark text-white border-secondary" placeholder="0.0000" min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label text-light">Meele Pieces</label>
                <input type="number" name="meele_pieces" id="meele_pieces"
                    class="form-control bg-dark text-white border-secondary" placeholder="0" min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label text-light">Estimated Value</label>
                <div class="input-group">
                    <span class="input-group-text bg-secondary border-secondary text-white">$</span>
                    <input type="number" step="0.01" name="meele_total_value" id="meele_total_value"
                        class="form-control bg-dark text-white border-secondary" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const parcelSelect = document.getElementById('meele_diamond_id');
        const caratInput = document.getElementById('meele_carat');
        const piecesInput = document.getElementById('meele_pieces');
        const valueInput = document.getElementById('meele_total_value');
        const infoText = document.getElementById('meele_parcel_info');
        const availWeight = document.getElementById('meele_avail_weight');
        const availPieces = document.getElementById('meele_avail_pieces');

        if (parcelSelect) {
            parcelSelect.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                if (selected.value) {
                    const weight = parseFloat(selected.dataset.weight);
                    const pieces = parseInt(selected.dataset.pieces);
                    const price = parseFloat(selected.dataset.price || 0);

                    availWeight.textContent = weight.toFixed(4);
                    availPieces.textContent = pieces;
                    infoText.classList.remove('d-none');

                    // Store price for calculation
                    parcelSelect.dataset.currentPrice = price;
                    calculateValue();

                    // Set max values
                    caratInput.max = weight;
                    piecesInput.max = pieces;
                } else {
                    infoText.classList.add('d-none');
                    caratInput.removeAttribute('max');
                    piecesInput.removeAttribute('max');
                    parcelSelect.dataset.currentPrice = 0;
                    valueInput.value = '';
                }
            });

            function calculateValue() {
                const price = parseFloat(parcelSelect.dataset.currentPrice || 0);
                const weight = parseFloat(caratInput.value || 0);
                if (price > 0 && weight > 0) {
                    valueInput.value = (price * weight).toFixed(2);
                }
            }

            caratInput.addEventListener('input', calculateValue);
        }
    });
</script>