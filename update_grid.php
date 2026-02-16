<?php
$file = 'resources/views/orders/index.blade.php';
$content = file_get_contents($file);

// 1. UPDATE CSS
// Can't match effectively via string replace due to whitespace issues.
// Let's use a unique string we know exists to find the location and replace a chunk.
$cssMarker = '/* Product Details Column (Global) */';
$cssStart = strpos($content, $cssMarker);
if ($cssStart === false) {
    die("CSS Marker not found\n");
}

// We want to replace from $cssStart until the next CSS rule or closing brace.
// The block seems to end at line 2411 (before .thumbnail-container img)
// Actually we want to replace the whole .thumbnail-container block.
// The next rule starts with ".thumbnail-container img"
$cssEndMarker = '.thumbnail-container img {';
$cssEnd = strpos($content, $cssEndMarker, $cssStart);

if ($cssEnd === false) {
    die("CSS End Marker not found\n");
}

// Check what we are replacing
$oldCss = substr($content, $cssStart, $cssEnd - $cssStart);
// echo "Old CSS: \n$oldCss\n";

$newCss = "/* Product Details Column (Global) */
        .thumbnail-wrapper {
            width: 100px;
            margin: 0 auto;
            position: relative;
        }

        .thumbnail-container {
            width: 100px;
            height: 100px;
            position: relative;
            border-radius: 8px;
            margin: 0 auto;
            border: 1px solid var(--border);
            background: #fff;
            transition: transform 0.2s ease, z-index 0s;
            z-index: 1;
        }

        .thumbnail-wrapper.grid-view {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2px;
            width: 102px;
        }

        .thumbnail-wrapper.grid-view .thumbnail-container {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            margin: 0;
            border: none;
        }

        .thumbnail-wrapper.grid-view .thumbnail-container:hover {
             transform: scale(3.5);
             box-shadow: 0 5px 15px rgba(0,0,0,0.3);
             z-index: 1000;
        }

        ";

$content = substr_replace($content, $newCss, $cssStart, $cssEnd - $cssStart);


// 2. UPDATE HTML/PHP LOGIC
// Find the block starting with <div class="thumbnail-container"> inside the loop
// We can search for the PHP block preceding it to be safe.
$phpMarker = '$skuText = !empty($skus) ? implode(\', \', $skus) : \'—\';';
$phpPos = strpos($content, $phpMarker);
if ($phpPos === false) {
    die("PHP Marker not found\n");
}

// Move past the PHP block end
$phpEndTag = '?>';
$phpEndPos = strpos($content, $phpEndTag, $phpPos);
// The HTML we want to replace starts after this.
// It is: <div class="thumbnail-container"> ... </div> (roughly lines 583-596)
// We want to replace everything from after the PHP block until the start of @if($skuText !== '—')

$htmlEndMarker = '@if($skuText !== \'—\')';
$htmlEndPos = strpos($content, $htmlEndMarker, $phpEndPos);

if ($htmlEndPos === false) {
    die("HTML End Marker not found\n");
}

// Adjust start position to be after the @endphp or just before the div
$searchStart = $phpEndPos + 2; // after ?>

// We need to inject the new PHP and HTML.
// Current PHP block ends with $skuText assignment. We need to add logic there too or just replace the whole block.
// Let's replace the whole column content div inner.

// Better approach: Find the whole <div class="d-flex flex-column align-items-center"> block content.
    // But that's hard to parse.

    // Let's replace the segment between the PHP start and the SKU text start.
    // This covers the PHP logic and the image display div.
    // Start: @php (around line 572)
    // End: @if($skuText (around line 598)

    $segmentStartMarker = '@php
    $firstImage = null;';
    // Normalize whitespace for search? No, let's try to match a unique string.
    // The detailed PHP block inside the loop.
    $segmentStartPos = strpos($content, '$firstImage = null;');
    if ($segmentStartPos === false) {
    // Try finding the container div
    $divStart = '<div class="d-flex flex-column align-items-center">';
        $divPos = strpos($content, $divStart);
        // There might be multiple. We want the one inside the loop.
        // The loop starts with @foreach ($orders as $order)
        // Let's search from there.
        $loopStart = strpos($content, '@foreach ($orders as $order)');
        $divPos = strpos($content, $divStart, $loopStart);

        if ($divPos === false) die("Div start not found");

        $segmentStartPos = $divPos + strlen($divStart);
        } else {
        // Backtrack to @php
        $segmentStartPos = strrpos(substr($content, 0, $segmentStartPos), '@php');
        }


        $newHtml = '
        @php
        $orderImages = [];
        if (!empty($order->images)) {
        $imgs = is_string($order->images) ? json_decode($order->images, true) : $order->images;
        if (is_array($imgs)) {
        $orderImages = array_slice($imgs, 0, 4);
        }
        }

        $imageCount = count($orderImages);
        $gridClass = $imageCount > 1 ? \'grid-view\' : \'\';

        $skus = is_array($order->diamond_skus) ? $order->diamond_skus : (!empty($order->diamond_sku) ?
        [$order->diamond_sku] : []);
        $skuText = !empty($skus) ? implode(\', \', $skus) : \'—\';
        @endphp

        <div class="thumbnail-wrapper {{ $gridClass }}">
            @if($imageCount > 0)
            @foreach($orderImages as $img)
            <div class="thumbnail-container">
                <img src="{{ $img[\'url\'] }}" alt="Product">
                <a href="{{ $img[\'url\'] }}" target="_blank" class="thumbnail-overlay" title="View Image">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
            @endforeach
            @else
            <div class="thumbnail-container">
                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                    <i class="bi bi-image" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            @endif
        </div>

        ';

        $content = substr_replace($content, $newHtml, $segmentStartPos, $htmlEndPos - $segmentStartPos);

        file_put_contents($file, $content);
        echo "Successfully updated Product Details logic and CSS.\n";
        ?>