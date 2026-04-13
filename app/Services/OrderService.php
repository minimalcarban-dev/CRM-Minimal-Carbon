<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Diamond;
use App\Models\JewelleryStock;
use App\Models\MeleeDiamond;
use App\Services\ShippingTrackingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Check order SKU availability across diamonds and jewellery stock.
     */
    public function checkOrderSkuAvailability(string $sku): array
    {
        $sku = strtoupper(trim($sku));

        if ($sku === '') {
            return [
                'available' => false,
                'type' => null,
                'item' => null,
                'details' => null,
                'message' => 'SKU is required',
            ];
        }

        $diamond = Diamond::where('sku', $sku)->first();
        if ($diamond) {
            if ($diamond->is_sold_out === 'Sold') {
                return [
                    'available' => false,
                    'type' => 'diamond',
                    'item' => $diamond,
                    'details' => [
                        'sku' => $diamond->sku,
                        'display_details' => trim(($diamond->weight ?? '?') . 'ct ' . ($diamond->shape ?? '')),
                        'carat' => $diamond->weight,
                        'shape' => $diamond->shape,
                        'clarity' => $diamond->clarity,
                        'color' => $diamond->color,
                        'listing_price' => $diamond->listing_price,
                        'stock_status' => 'sold',
                    ],
                    'message' => 'Diamond "' . $sku . '" is already sold. Please remove it and select a different SKU.',
                ];
            }

            return [
                'available' => true,
                'type' => 'diamond',
                'item' => $diamond,
                'message' => 'Diamond SKU available',
                'details' => [
                    'sku' => $diamond->sku,
                    'display_details' => trim(($diamond->weight ?? '?') . 'ct ' . ($diamond->shape ?? '')),
                    'carat' => $diamond->weight,
                    'shape' => $diamond->shape,
                    'clarity' => $diamond->clarity,
                    'color' => $diamond->color,
                    'listing_price' => $diamond->listing_price,
                ],
            ];
        }

        $jewellery = JewelleryStock::where('sku', $sku)->first();
        if ($jewellery) {
            if ((int) $jewellery->quantity <= 0 || $jewellery->status === 'out_of_stock') {
                return [
                    'available' => false,
                    'type' => 'jewellery',
                    'item' => $jewellery,
                    'details' => [
                        'sku' => $jewellery->sku,
                        'display_details' => trim(($jewellery->name ?? 'Jewellery') . ' ' . (!empty($jewellery->type) ? '(' . $jewellery->type . ')' : '')),
                        'name' => $jewellery->name,
                        'type' => $jewellery->type,
                        'quantity' => (int) $jewellery->quantity,
                        'selling_price' => $jewellery->selling_price,
                        'stock_status' => 'out_of_stock',
                    ],
                    'message' => 'Jewellery SKU "' . $sku . '" is out of stock.',
                ];
            }

            return [
                'available' => true,
                'type' => 'jewellery',
                'item' => $jewellery,
                'message' => 'Jewellery SKU available',
                'details' => [
                    'sku' => $jewellery->sku,
                    'display_details' => trim(($jewellery->name ?? 'Jewellery') . ' ' . (!empty($jewellery->type) ? '(' . $jewellery->type . ')' : '')),
                    'name' => $jewellery->name,
                    'type' => $jewellery->type,
                    'quantity' => (int) $jewellery->quantity,
                    'selling_price' => $jewellery->selling_price,
                ],
            ];
        }

        return [
            'available' => false,
            'type' => null,
            'item' => null,
            'details' => null,
            'message' => 'SKU "' . $sku . '" not found in diamond or jewellery stock.',
        ];
    }

    /**
     * Extract normalized SKU list from validated payload.
     */
    public function extractValidatedSkus(array $validated): array
    {
        $rawSkus = [];

        if (!empty($validated['diamond_skus']) && is_array($validated['diamond_skus'])) {
            $rawSkus = array_merge($rawSkus, $validated['diamond_skus']);
        }

        if (!empty($validated['diamond_sku'])) {
            $rawSkus[] = $validated['diamond_sku'];
        }

        // Extract potentially hidden SKUs from free-text fields
        foreach (['product_other', 'jewellery_details'] as $field) {
            if (!empty($validated[$field]) && is_string($validated[$field])) {
                // Regex matches alphanumeric codes starting with SKU- (e.g. SKU-1234)
                if (preg_match_all('/\b(SKU-[A-Z0-9-]{3,20})\b/i', $validated[$field], $matches)) {
                    $rawSkus = array_merge($rawSkus, $matches[1]);
                }
            }
        }

        $normalized = array_map(
            static fn($sku) => strtoupper(trim((string) $sku)),
            $rawSkus
        );

        return array_values(array_unique(array_filter($normalized)));
    }

    /**
     * Extract normalized SKU list from existing order.
     */
    public function extractOrderSkus(Order $order): array
    {
        $rawSkus = [];

        if (!empty($order->diamond_skus) && is_array($order->diamond_skus)) {
            $rawSkus = array_merge($rawSkus, $order->diamond_skus);
        }

        if (!empty($order->diamond_sku)) {
            $rawSkus[] = $order->diamond_sku;
        }

        $normalized = array_map(
            static fn($sku) => strtoupper(trim((string) $sku)),
            $rawSkus
        );

        return array_values(array_unique(array_filter($normalized)));
    }

    /**
     * Normalize incoming melee payload.
     */
    public function extractValidatedMeleeEntries(array $validated): array
    {
        $entries = [];

        if (!empty($validated['melee_entries']) && is_array($validated['melee_entries'])) {
            $entries = $validated['melee_entries'];
        } elseif (!empty($validated['melee_entries_json'])) {
            $decoded = json_decode($validated['melee_entries_json'], true);
            if (is_array($decoded)) {
                $entries = $decoded;
            }
        } elseif (!empty($validated['melee_diamond_id']) && !empty($validated['melee_pieces'])) {
            $entries = [
                [
                    'melee_diamond_id' => $validated['melee_diamond_id'],
                    'pieces' => $validated['melee_pieces'],
                ]
            ];
        }

        return array_values(array_filter($entries, function ($entry) {
            return !empty($entry['melee_diamond_id']) && (int) ($entry['pieces'] ?? 0) > 0;
        }));
    }

    /**
     * Normalize melee entries stored on an order or snapshot, with legacy fallback support.
     *
     * @param mixed $entries
     * @return array<int, array<string, mixed>>
     */
    public function normalizeStoredMeleeEntries(
        $entries,
        $fallbackDiamondId = null,
        $fallbackPieces = null,
        $fallbackCarat = null,
        $fallbackPricePerCt = null
    ): array {
        if (is_string($entries)) {
            $decoded = json_decode($entries, true);
            $entries = is_array($decoded) ? $decoded : [];
        }

        if (empty($entries) && !empty($fallbackDiamondId) && (int) $fallbackPieces > 0) {
            $entries = [
                [
                    'melee_diamond_id' => (int) $fallbackDiamondId,
                    'pieces' => (int) $fallbackPieces,
                    'avg_carat_per_piece' => (int) $fallbackPieces > 0
                        ? round((float) $fallbackCarat / (int) $fallbackPieces, 5)
                        : 0,
                    'price_per_ct' => (float) ($fallbackPricePerCt ?? 0),
                ]
            ];
        }

        return array_values(array_filter(array_map(function ($entry) {
            return [
                'melee_diamond_id' => (int) ($entry['melee_diamond_id'] ?? 0),
                'pieces' => (int) ($entry['pieces'] ?? 0),
                'avg_carat_per_piece' => round((float) ($entry['avg_carat_per_piece'] ?? 0), 5),
                'price_per_ct' => (float) ($entry['price_per_ct'] ?? 0),
            ];
        }, (array) $entries), function ($entry) {
            return $entry['melee_diamond_id'] > 0 && $entry['pieces'] > 0;
        }));
    }

    /**
     * Normalize melee entries directly from the persisted order record.
     *
     * @return array<int, array<string, mixed>>
     */
    public function extractStoredMeleeEntries(Order $order): array
    {
        return $this->normalizeStoredMeleeEntries(
            $order->melee_entries,
            $order->melee_diamond_id,
            $order->melee_pieces,
            $order->melee_carat,
            $order->melee_price_per_ct
        );
    }

    /**
     * Normalize melee entries from an old audit snapshot.
     *
     * @param array<string, mixed> $snapshot
     * @return array<int, array<string, mixed>>
     */
    public function extractSnapshotMeleeEntries(array $snapshot): array
    {
        return $this->normalizeStoredMeleeEntries(
            $snapshot['melee_entries'] ?? [],
            $snapshot['melee_diamond_id'] ?? null,
            $snapshot['melee_pieces'] ?? null,
            $snapshot['melee_carat'] ?? null,
            $snapshot['melee_price_per_ct'] ?? null
        );
    }

    /**
     * Validate requested melee pieces against live stock.
     */
    public function validateMeleeStockAvailability(
        array $incomingEntries,
        ?Order $existingOrder = null,
        bool $allowNegative = false
    ): void {
        if (empty($incomingEntries)) {
            return;
        }

        $requestedByMeleeId = [];
        $entryIndexesByMeleeId = [];

        foreach ($incomingEntries as $index => $entry) {
            $meleeId = (int) ($entry['melee_diamond_id'] ?? 0);
            $pieces = (int) ($entry['pieces'] ?? 0);

            if ($meleeId <= 0 || $pieces <= 0) {
                continue;
            }

            $requestedByMeleeId[$meleeId] = ($requestedByMeleeId[$meleeId] ?? 0) + $pieces;
            $entryIndexesByMeleeId[$meleeId][] = $index;
        }

        if (empty($requestedByMeleeId)) {
            return;
        }

        $reservedByMeleeId = $this->getReservedMeleePiecesFromOrder($existingOrder);
        $diamonds = MeleeDiamond::with('category')
            ->whereIn('id', array_keys($requestedByMeleeId))
            ->get()
            ->keyBy('id');

        $errors = [];

        foreach ($requestedByMeleeId as $meleeId => $requestedPieces) {
            $diamond = $diamonds->get($meleeId);
            $affectedIndexes = $entryIndexesByMeleeId[$meleeId] ?? [];

            if (!$diamond) {
                foreach ($affectedIndexes as $index) {
                    $errors["melee_entries.$index.melee_diamond_id"] = 'Selected melee stock lot was not found.';
                }
                continue;
            }

            $allowedPieces = (int) $diamond->available_pieces + (int) ($reservedByMeleeId[$meleeId] ?? 0);

            if (!$allowNegative && $requestedPieces > $allowedPieces) {
                $label = $this->buildMeleeStockLabel($diamond);
                $message = "{$label} has only {$allowedPieces} pieces available in stock.";

                foreach ($affectedIndexes as $index) {
                    $errors["melee_entries.$index.pieces"] = $message;
                }

                $errors['melee_entries_json'] = $message;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Get existing order reservation.
     */
    public function getReservedMeleePiecesFromOrder(?Order $order): array
    {
        if (!$order) {
            return [];
        }

        $entries = $this->extractStoredMeleeEntries($order);

        $reserved = [];
        foreach ($entries as $entry) {
            $meleeId = (int) ($entry['melee_diamond_id'] ?? 0);
            $pieces = (int) ($entry['pieces'] ?? 0);

            if ($meleeId <= 0 || $pieces <= 0) {
                continue;
            }

            $reserved[$meleeId] = ($reserved[$meleeId] ?? 0) + $pieces;
        }

        return $reserved;
    }

    /**
     * Build melee stock label.
     */
    public function buildMeleeStockLabel(MeleeDiamond $diamond): string
    {
        $category = optional($diamond->category)->name ?? 'Melee';
        $shape = trim((string) $diamond->shape);
        $size = trim((string) $diamond->size_label);

        return trim("{$category} - {$shape} {$size}");
    }

    /**
     * Assign common validated fields to Order model.
     */
    public function assignOrderFields(Order $order, array $validated): void
    {
        $order->order_type = $validated['order_type'];
        $order->company_id = $validated['company_id'];
        $order->factory_id = !empty($validated['factory_id']) ? $validated['factory_id'] : null;

        $order->client_name = $validated['client_name'] ?? '';
        $order->client_address = $validated['client_address'] ?? '';
        $order->client_mobile = $validated['client_mobile'] ?? '';
        $order->client_tax_id = $validated['client_tax_id'] ?? '';
        $order->client_tax_id_type = $validated['client_tax_id_type'] ?? null;
        $order->client_email = $validated['client_email'] ?? '';
        $order->jewellery_details = $validated['jewellery_details'] ?? '';
        $order->diamond_details = $validated['diamond_details'] ?? '';
        $order->diamond_sku = $validated['diamond_sku'] ?? '';

        // Handle multiple diamond SKUs
        if (!empty($validated['diamond_skus'])) {
            $skus = array_filter(array_unique($validated['diamond_skus']));
            $order->diamond_skus = $skus;
            if (empty($order->diamond_sku) && !empty($skus)) {
                $order->diamond_sku = $skus[0];
            }
        } elseif (!empty($validated['diamond_sku'])) {
            $order->diamond_skus = [$validated['diamond_sku']];
        }

        if (!empty($validated['diamond_prices'])) {
            $order->diamond_prices = $validated['diamond_prices'];
        }

        $order->product_other = $validated['product_other'] ?? '';
        $order->special_notes = $validated['special_notes'] ?? '';
        $order->shipping_company_name = $validated['shipping_company_name'] ?? '';
        $order->tracking_number = $validated['tracking_number'] ?? '';

        $trackingUrl = $validated['tracking_url'] ?? '';
        if (empty($trackingUrl) && !empty($order->tracking_number) && !empty($order->shipping_company_name)) {
            $trackingService = new ShippingTrackingService();
            $trackingUrl = $trackingService->generateTrackingUrl($order->shipping_company_name, $order->tracking_number);
        }
        $order->tracking_url = $trackingUrl;

        $order->gold_detail_id = !empty($validated['gold_detail_id']) ? $validated['gold_detail_id'] : null;
        $order->ring_size_id = !empty($validated['ring_size_id']) ? $validated['ring_size_id'] : null;
        $order->setting_type_id = !empty($validated['setting_type_id']) ? $validated['setting_type_id'] : null;
        $order->earring_type_id = !empty($validated['earring_type_id']) ? $validated['earring_type_id'] : null;

        // Melee Fields
        $meleeEntries = [];
        if (!empty($validated['melee_entries'])) {
            $meleeEntries = $validated['melee_entries'];
        } elseif (!empty($validated['melee_entries_json'])) {
            $decoded = json_decode($validated['melee_entries_json'], true);
            if (is_array($decoded)) {
                $meleeEntries = $decoded;
            }
        }

        $order->melee_entries = !empty($meleeEntries) ? $meleeEntries : null;

        if (!empty($meleeEntries)) {
            $first = $meleeEntries[0];
            $totalPieces = array_sum(array_column($meleeEntries, 'pieces'));
            $totalCarat = 0;
            foreach ($meleeEntries as $entry) {
                $totalCarat += ($entry['pieces'] ?? 0) * ($entry['avg_carat_per_piece'] ?? 0);
            }
            $order->melee_diamond_id = $first['melee_diamond_id'] ?? null;
            $order->melee_pieces = $totalPieces;
            $order->melee_carat = round($totalCarat, 3);
            $order->melee_price_per_ct = $first['price_per_ct'] ?? null;
        } else {
            $order->melee_diamond_id = !empty($validated['melee_diamond_id']) ? $validated['melee_diamond_id'] : null;
            $order->melee_pieces = !empty($validated['melee_pieces']) ? $validated['melee_pieces'] : null;
            $order->melee_carat = !empty($validated['melee_carat']) ? $validated['melee_carat'] : null;
            $order->melee_price_per_ct = !empty($validated['melee_price_per_ct']) ? $validated['melee_price_per_ct'] : null;
        }

        if ($order->melee_carat && $order->melee_price_per_ct) {
            $order->melee_total_value = $order->melee_carat * $order->melee_price_per_ct;
        } else {
            $order->melee_total_value = 0;
        }

        $order->diamond_status = !empty($validated['diamond_status']) ? $validated['diamond_status'] : null;
        $order->note = !empty($validated['note']) ? $validated['note'] : null;

        $order->gross_sell = $validated['gross_sell'] ?? 0;

        $admin = Auth::guard('admin')->user();
        if (array_key_exists('gold_net_weight', $validated)) {
            if ($admin && ($admin->is_super || $admin->hasPermission('orders.add_gold_weight'))) {
                $order->gold_net_weight = $validated['gold_net_weight'];
            }
        }

        $order->dispatch_date = !empty($validated['dispatch_date']) ? $validated['dispatch_date'] : null;
    }
}
