@extends('layouts.admin')

@section('title', 'Suspicious Gold Rates')

@section('content')
    <div class="diamond-management-container tracker-page">
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('gold-tracking.index') }}" class="breadcrumb-link">Gold Tracking</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Suspicious Rates</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-exclamation-triangle" style="color: #ef4444;"></i>
                        Suspicious Gold Rates
                    </h1>
                    <p class="page-subtitle">Review flagged purchases and manually correct rate with audit log</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('gold-tracking.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="tracker-table-card" style="padding: 1rem 1.25rem; margin-bottom: 1rem;">
            <div style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                <strong>Rules:</strong>
                outside {{ (int) ($outlierMinFactor * 100) }}%-{{ (int) ($outlierMaxFactor * 100) }}% of reference rate OR
                outside absolute range &#8377;1000-&#8377;20000 per gram.
                @if ($todayRatePayload['is_available'] ?? false)
                    Current live reference:
                    <strong>&#8377;{{ number_format((float) ($todayRatePayload['rate_inr_per_gram'] ?? 0), 2) }}/gm</strong>.
                @else
                    Current live reference unavailable.
                @endif
            </div>
        </div>

        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Purchase</th>
                            <th>Current Rate</th>
                            <th>Reference Rate</th>
                            <th>Deviation</th>
                            <th>Reason</th>
                            <th>Correct</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suspiciousItems as $item)
                            @php $purchase = $item['purchase']; @endphp
                            <tr>
                                <td>{{ $purchase->purchase_date?->format('d-M-Y') }}</td>
                                <td>
                                    <div>#{{ $purchase->id }} · {{ $purchase->supplier_name }}</div>
                                    <small style="color:#64748b;">{{ number_format((float) $purchase->weight_grams, 3) }}
                                        gm</small>
                                </td>
                                <td style="color:#dc2626; font-weight:700;">
                                    &#8377;{{ number_format((float) $purchase->rate_per_gram, 2) }}/gm
                                </td>
                                <td>
                                    @if ($item['reference_rate'])
                                        &#8377;{{ number_format((float) $item['reference_rate'], 2) }}/gm
                                        <div><small style="color:#64748b;">{{ $item['reference_source'] }}</small></div>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!is_null($item['deviation_percent']))
                                        {{ number_format((float) $item['deviation_percent'], 2) }}%
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td style="max-width: 280px;">{{ $item['reason'] }}</td>
                                <td style="min-width: 320px;">
                                    <form action="{{ route('gold-tracking.purchases.correct-rate', $purchase) }}"
                                        method="POST">
                                        @csrf
                                        <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                                            <input type="number" step="0.01" min="0.01" class="form-control"
                                                name="new_rate_per_gram"
                                                value="{{ old('new_rate_per_gram', $item['reference_rate'] ? number_format((float) $item['reference_rate'], 2, '.', '') : number_format((float) $purchase->rate_per_gram, 2, '.', '')) }}"
                                                placeholder="New rate/g" required>
                                            <button type="submit" class="btn-primary-custom" style="white-space:nowrap;">
                                                Save
                                            </button>
                                        </div>
                                        <input type="text" class="form-control" name="correction_note"
                                            value="{{ old('correction_note') }}"
                                            placeholder="Correction note (required for audit)" required>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-check2-circle"></i></div>
                                        <h3 class="tracker-empty-title">No suspicious rates found</h3>
                                        <p class="tracker-empty-desc">All completed gold purchases are within configured
                                            thresholds.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
