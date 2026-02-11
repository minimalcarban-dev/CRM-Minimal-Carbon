<div class="table-responsive">
    <table class="table align-middle">
        <thead class="bg-light">
            <tr>
                <th class="ps-4">Shape</th>
                <th>Color</th>
                <th>Size Label</th>
                <th>Sieve</th>
                <th class="text-center">Stock (Pcs)</th>
                <th class="text-center">Avg $/Ct</th>
                <th class="text-end pe-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diamonds as $diamond)
                <tr class="stock-row" data-shape="{{ $diamond->shape }}">
                    <td class="ps-4 fw-medium">{{ $diamond->shape }}</td>
                    <td>{{ $diamond->color ?? '-' }}</td>
                    <td>{{ $diamond->size_label }}</td>
                    <td class="text-muted">{{ $diamond->sieve_size ?? '-' }}</td>
                    <td class="text-center">
                        <span
                            class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill">
                            {{ $diamond->available_pieces }}
                        </span>
                    </td>
                    <td class="text-center">${{ number_format($diamond->purchase_price_per_ct, 2) }}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-success border" onclick="openTransactionModal('in', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $diamond->category->name }}')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger border ms-1" onclick="openTransactionModal('out', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $diamond->category->name }}')">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>