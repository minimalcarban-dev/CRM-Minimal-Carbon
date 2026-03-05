@extends('layouts.admin')

@section('title', 'Shopify Settings')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--dark);">
                <i class="bi bi-shop me-2" style="color: var(--primary);"></i>Shopify Settings
            </h4>
            <p class="text-muted mb-0">Manage your Shopify store connection and API credentials</p>
        </div>
        <div>
            @if($isConnected)
                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i>Connected</span>
            @else
                <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle me-1"></i>Not Connected</span>
            @endif
        </div>
    </div>

    {{-- Connection Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <form action="{{ route('shopify.settings.save') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Store URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">https://</span>
                            <input type="text" class="form-control" name="store_url"
                                   value="{{ old('store_url', $setting->store_url ?? config('shopify.store_url')) }}"
                                   placeholder="yourstore.myshopify.com" required>
                        </div>
                        <small class="text-muted">e.g. minimalcarbon.myshopify.com</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">API Version</label>
                        <input type="text" class="form-control" name="api_version"
                               value="{{ old('api_version', $setting->api_version ?? config('shopify.api_version')) }}"
                               placeholder="2024-01">
                        <small class="text-muted">Shopify API version (e.g. 2024-01)</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Admin API Access Token</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" name="access_token" id="accessTokenInput"
                                   value="{{ old('access_token', $setting ? '••••••••••••••••••••' : '') }}"
                                   placeholder="shpat_xxxxxxxxxxxxxxxxxxxxxxxx" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleToken()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            Starts with <code>shpat_</code>. Found in Shopify Admin → Settings → Apps → [Your App] → API credentials.
                        </small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Save Settings
                    </button>

                    <form action="{{ route('shopify.test-connection') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success px-4">
                            <i class="bi bi-lightning me-2"></i>Test Connection
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="row mt-4 g-3">
        <div class="col-md-4">
            <a href="{{ route('shopify.products') }}" class="card border-0 shadow-sm text-decoration-none h-100" style="border-radius: 14px;">
                <div class="card-body text-center py-4">
                    <div class="mb-3" style="font-size: 2rem; color: var(--primary);"><i class="bi bi-box-seam"></i></div>
                    <h6 class="fw-bold mb-1" style="color: var(--dark);">Products</h6>
                    <small class="text-muted">Import & manage Shopify products</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('shopify.collections') }}" class="card border-0 shadow-sm text-decoration-none h-100" style="border-radius: 14px;">
                <div class="card-body text-center py-4">
                    <div class="mb-3" style="font-size: 2rem; color: var(--success);"><i class="bi bi-collection"></i></div>
                    <h6 class="fw-bold mb-1" style="color: var(--dark);">Collections</h6>
                    <small class="text-muted">Sync Shopify collections</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('shopify.logs') }}" class="card border-0 shadow-sm text-decoration-none h-100" style="border-radius: 14px;">
                <div class="card-body text-center py-4">
                    <div class="mb-3" style="font-size: 2rem; color: var(--warning);"><i class="bi bi-journal-text"></i></div>
                    <h6 class="fw-bold mb-1" style="color: var(--dark);">Sync Logs</h6>
                    <small class="text-muted">View API activity logs</small>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleToken() {
    const input = document.getElementById('accessTokenInput');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endpush
