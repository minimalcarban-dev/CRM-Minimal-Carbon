<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Extracts custom metafield values from Shopify product description HTML.
 *
 * Designed for the jewellery domain — parses structured attributes like
 * "Metal Purity: 18K", "Stone Clarity: VS1", etc. from product body_html.
 */
class ShopifyMetafieldExtractor
{
    /**
     * Mapping from human-readable labels (as they appear in descriptions)
     * to database column names.
     */
    protected static array $labelMap = [
        'Metal Purity' => 'metal_purity',
        'Metal' => 'metal',
        'Resizable' => 'resizable',
        'Comfort Fit' => 'comfort_fit',
        'Ring Height 1' => 'ring_height_1',
        'Ring Width 1' => 'ring_width_1',
        'Product Video' => 'product_video',
        'Stone Measurement' => 'stone_measurement',
        'Stone Clarity' => 'stone_clarity',
        'Stone Carat Weight' => 'stone_carat_weight',
        'Stone Color' => 'stone_color',
        'Stone Shape' => 'stone_shape',
        'Stone Type' => 'stone_type',
        'Side Stone Type' => 'side_stone_type',
        'Side Shape' => 'side_shape',
        'Side Color' => 'side_color',
        'Side Carat Weight' => 'side_carat_weight',
        'Side Measurement' => 'side_measurement',
        'Side Clarity' => 'side_clarity',
        'melee_size' => 'melee_size',
        'Melee Size' => 'melee_size',
    ];

    /**
     * Extract all recognisable metafields from a product description.
     *
     * @param  string|null $html  The body_html from Shopify
     * @return array  Associative array of column_name => value (nulls excluded)
     */
    public static function extract(?string $html): array
    {
        if (empty($html)) {
            return [];
        }

        // Strip HTML tags and decode entities to plain text
        $text = html_entity_decode(strip_tags(
            str_ireplace(['<br>', '<br/>', '<br />', '</li>', '</p>', '</td>', '</tr>'], "\n", $html)
        ), ENT_QUOTES, 'UTF-8');

        $extracted = [];

        foreach (self::$labelMap as $label => $column) {
            // Already found this column via a different label alias
            if (isset($extracted[$column]) && $extracted[$column] !== null) {
                continue;
            }

            $value = self::findValue($text, $label);
            if ($value !== null) {
                $extracted[$column] = $value;
            }
        }

        return $extracted;
    }

    /**
     * Extract metafields from Shopify's metafield API response array.
     * This handles metafields fetched directly from the API (not description).
     *
     * @param  array $metafields  Array of metafield objects from Shopify API
     * @return array  Associative array of column_name => value
     */
    public static function extractFromMetafieldApi(array $metafields): array
    {
        $validColumns = array_values(self::$labelMap);
        $extracted = [];

        foreach ($metafields as $mf) {
            $key = $mf['key'] ?? '';
            $value = $mf['value'] ?? null;

            // Direct match by key name
            if (in_array($key, $validColumns, true) && $value !== null) {
                $extracted[$key] = $value;
                continue;
            }

            // Try matching via label map (when Shopify key uses the display label)
            $mapped = self::$labelMap[$key] ?? null;
            if ($mapped && $value !== null) {
                $extracted[$mapped] = $value;
            }
        }

        return $extracted;
    }

    /*
     |--------------------------------------------------------------------------
     | Internal Helpers
     |--------------------------------------------------------------------------
     */

    /**
     * Find a value for a given label in the text using multiple patterns.
     */
    protected static function findValue(string $text, string $label): ?string
    {
        // Escape label for regex
        $escaped = preg_quote($label, '/');

        // Pattern 1: "Label: Value" or "Label : Value"
        if (preg_match("/{$escaped}\s*[:=]\s*(.+?)(?:\n|\||$)/i", $text, $m)) {
            $val = trim($m[1]);
            if ($val !== '') {
                return $val;
            }
        }

        // Pattern 2: "Label - Value"
        if (preg_match("/{$escaped}\s*-\s*(.+?)(?:\n|\||$)/i", $text, $m)) {
            $val = trim($m[1]);
            if ($val !== '') {
                return $val;
            }
        }

        return null;
    }
}
