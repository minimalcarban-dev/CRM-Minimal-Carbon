@php
    // Extract images and PDFs from order metadata
    // This partial expects an $order object and a $type parameter ('images' or 'pdfs')
    
    $images = [];
    $pdfs = [];
    
    if (isset($order)) {
        // Extract images - Handle both string arrays and structured arrays with metadata
        $rawImages = $order->images ?? [];
        if (is_string($rawImages)) {
            $rawImages = json_decode($rawImages, true) ?: [];
        }
        
        if (is_array($rawImages)) {
            foreach ($rawImages as $img) {
                $images[] = is_array($img) ? ($img['url'] ?? '') : $img;
            }
        }

        // Extract PDFs
        $rawPdfs = $order->order_pdfs ?? [];
        if (is_string($rawPdfs)) {
            $rawPdfs = json_decode($rawPdfs, true) ?: [];
        }
        
        if (is_array($rawPdfs)) {
            foreach ($rawPdfs as $p) {
                if (empty($p)) continue;
                $pdfs[] = [
                    'url' => is_array($p) ? ($p['url'] ?? '') : $p,
                    'name' => is_array($p) ? ($p['name'] ?? 'Document') : basename($p),
                    'size' => is_array($p) ? ($p['size'] ?? 0) : 0
                ];
            }
        }
    }
@endphp

@if($type === 'images' && !empty($images))
    <div class="mt-3">
        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-light-primary text-primary me-2">
                <i class="bi bi-images me-1"></i> {{ count($images) }} Existing
            </span>
        </div>
        <div class="od-img-grid">
            @foreach($images as $index => $url)
                @if(!empty($url))
                    <div class="od-img-item" id="img-{{ $index }}">
                        <img src="{{ asset($url) }}" alt="Order Image" onclick="viewImage('{{ asset($url) }}', 'Order Image')">
                        <div class="od-img-overlay" onclick="viewImage('{{ asset($url) }}', 'Order Image')">
                            <i class="bi bi-zoom-in"></i>
                        </div>
                        <button type="button" class="remove-existing-file" 
                                onclick="removeExistingFile('{{ $url }}', 'image', 'img-{{ $index }}', event)" 
                                title="Remove Image">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

@if($type === 'pdfs' && !empty($pdfs))
    <div class="mt-3">
        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-light-danger text-danger me-2">
                <i class="bi bi-file-pdf me-1"></i> {{ count($pdfs) }} Existing
            </span>
        </div>
        <div class="current-pdfs-list">
            @foreach($pdfs as $index => $pdf)
                @php
                    $pdfPath = $pdf['url'] ?? '';
                    $pdfName = $pdf['name'] ?? 'Document';
                    $pdfSize = $pdf['size'] ?? 0;
                    $pdfFullUrl = asset($pdfPath);
                    $sizeFormatted = $pdfSize > 0 ? number_format($pdfSize / 1048576, 2) . ' MB' : '0.27 MB';
                @endphp
                @if(!empty($pdfPath))
                    <div class="od-pdf-item" id="pdf-{{ $index }}">
                        <div class="od-pdf-icon">
                            <i class="bi bi-file-pdf"></i>
                        </div>
                        <div class="od-pdf-info" style="cursor: pointer;" onclick="viewPDF('{{ $pdfFullUrl }}', '{{ str_replace("'", "\\'", $pdfName) }}')">
                            <p class="od-pdf-name text-truncate" title="{{ $pdfName }}">{{ $pdfName }}</p>
                            <span class="od-pdf-size">{{ $sizeFormatted }}</span>
                        </div>
                        <div class="od-pdf-actions">
                            <button type="button" class="od-pdf-btn" title="View" onclick="viewPDF('{{ $pdfFullUrl }}', '{{ str_replace("'", "\\'", $pdfName) }}')">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="od-pdf-btn" title="Download" onclick="downloadPDF('{{ $pdfFullUrl }}', '{{ str_replace("'", "\\'", $pdfName) }}')">
                                <i class="bi bi-download"></i>
                            </button>
                            <button type="button" class="od-pdf-btn remove-pdf-btn" title="Remove" 
                                    onclick="removeExistingFile('{{ $pdfPath }}', 'pdf', 'pdf-{{ $index }}', event)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
