<?php
$file = 'resources/views/orders/index.blade.php';
$content = file_get_contents($file);

// Block to Move (Product Details at Col 4 position)
// We need to capture from <td>...</td>
// Line 579 is <td> followed by <div class="d-flex flex-column align-items-center">
// Line 616 is </td>

// Let's find the start position
$startSearch = '                                    <td>
                                        <div class="d-flex flex-column align-items-center">';
$posStart = strpos($content, $startSearch);

if ($posStart === false) {
    die("Error: Could not find start of Product Details block.\n");
}

// Find the end: </td> followed by text
// It ends at line 616. The next line (617) is <td> for order type logic.
// However, the block ends with </td> then newline/spacing then <td>
$endSearch = '                                    </td>
                                    <td>
                                        @if($order->order_type == \'ready_to_ship\')';

$posEnd = strpos($content, $endSearch, $posStart);
if ($posEnd === false) {
    // Try matching just the closing td context if strict match fails
    $endSearchLoose = '                                    </td>
                                    <td>';
    $posEnd = strpos($content, $endSearchLoose, $posStart);
    if ($posEnd === false) {
        die("Error: Could not find end of Product Details block.\n");
    }
}

// Extract the block
// We want to extract up to the end of the closing </td>
// $posEnd points to the start of "                                    </td>"
// So we need to include "                                    </td>" length into extraction?
// Wait, $endSearch starts with the closing </td>. So $posEnd is the start of the closing tag line.
// We want to capture the closing tag line too.
// The length of "                                    </td>" is 42 chars (36 spaces + 5 chars + newline)
// Easier: calculate length to capture.
$closingTag = "                                    </td>";
$length = ($posEnd + strlen($closingTag)) - $posStart;

$block = substr($content, $posStart, $length);
echo "Captured block:\n" . substr($block, 0, 100) . "...\n";

// Remove the block from content
// Important: remove the newline after it too if possible to keep consistent spacing
$content = substr_replace($content, '', $posStart, $length);

// Now finding insertion point: After ID column (</td>)
$idEndSearch = '                                    <td class="td-id">
                                        <span class="order-id-badge">#{{ $order->id }}</span>
                                    </td>';
$posIdEnd = strpos($content, $idEndSearch);
if ($posIdEnd === false) {
    die("Error: Could not find ID column block.\n");
}
$posIdEnd += strlen($idEndSearch); // Point to after the ID block

// Insert the block
// Add a newline before inserting to maintain formatting
$content = substr_replace($content, "\n" . $block, $posIdEnd, 0);

// Write back
file_put_contents($file, $content);
echo "Successfully moved Product Details column.\n";
?>