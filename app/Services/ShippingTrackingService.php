<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShippingTrackingService
{
    /**
     * Synchronize tracking data for an order
     */
    public function syncOrderTracking(Order $order)
    {
        // 1. Identify Tracking Number and Carrier
        $trackingNumber = $order->tracking_number;
        $carrierCode = $this->detectCarrierCode($order->shipping_company_name);

        // Fallback: Try to extract from URL if number is missing
        if (empty($trackingNumber) && !empty($order->tracking_url)) {
            $details = $this->extractTrackingDetails($order->tracking_url);
            $trackingNumber = $details['number'] ?? null;
            if (empty($carrierCode)) {
                $carrierCode = $details['carrier'] ?? null;
            }
        }

        if (empty($trackingNumber)) {
            return [
                'success' => false,
                'message' => 'Tracking number is missing. Please add a tracking number or URL.'
            ];
        }

        // 2. Fetch from ParcelsApp
        $apiKey = config('services.parcelsapp.api_key');
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'ParcelsApp API Key is missing.'
            ];
        }

        $trackingData = $this->fetchFromParcelsApp($trackingNumber, $apiKey, $carrierCode);

        if ($trackingData['success']) {
            $oldSnapshot = $this->captureTrackingSnapshot($order);

            $order->update([
                'tracking_status' => $trackingData['status'],
                'tracking_history' => $trackingData['history'],
                'last_tracker_sync' => now(),
                'tracking_number' => $trackingNumber, // Ensure number is saved if extracted
                'shipping_company_name' => $order->shipping_company_name ?: ($trackingData['carrier'] ?? 'Unknown Carrier'),
            ]);

            $this->logTrackingAuditChanges($order->fresh(), $oldSnapshot, 'tracking_synced');

            return [
                'success' => true,
                'message' => 'Tracking synced successfully.',
                'data' => $trackingData
            ];
        }

        return [
            'success' => false,
            'message' => $trackingData['message'] ?? 'Failed to sync tracking.'
        ];
    }

    /**
     * Map company name to courier code
     */
    private function detectCarrierCode($companyName)
    {
        if (empty($companyName))
            return null;

        $name = strtolower(trim($companyName));

        // Mapping common names to standard courier codes
        $carriers = [
            'aramex' => 'aramex',
            'aramax' => 'aramex',
            'dhl' => 'dhl',
            'fedex' => 'fedex',
            'ups' => 'ups',
            'usps' => 'usps',
            'tnt' => 'tnt',
            'ems' => 'ems',
            'india post' => 'india-post',
            'speed post' => 'india-post',
            'emirates post' => 'emirates-post',
            'royal mail' => 'royal-mail',
            'blue dart' => 'bluedart',
            'dtdc' => 'dtdc',
            'delhivery' => 'delhivery',
            'china post' => 'china-post',
            'singapore post' => 'singapore-post',
            'hong kong post' => 'hong-kong-post',
            'postnl' => 'postnl',
            'canada post' => 'canada-post',
            'australia post' => 'australia-post',
            'la poste' => 'la-poste',
            'deutsche post' => 'deutsche-post',
            'dpd' => 'dpd',
            'gls' => 'gls',
            'hermes' => 'hermes',
            'sf express' => 'sf-express',
            'yanwen' => 'yanwen',
            'yunexpress' => 'yun-express',
            '4px' => '4px',
            'cainiao' => 'cainiao',
            'landmark global' => 'landmark-global',
            'skynet' => 'skynet',
            'j&t' => 'jt-express',
            'ninja van' => 'ninjavan',
            'kerry' => 'kerry-express',
            'flash' => 'flash-express',
            'an post' => 'an-post',
            'bpost' => 'bpost',
            'pos malaysia' => 'pos-malaysia',
            'thailand post' => 'thailand-post',
        ];

        foreach ($carriers as $key => $code) {
            if (str_contains($name, $key)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Generate a tracking URL (Frontend link)
     */
    public function generateTrackingUrl($carrier, $number)
    {
        if (empty($number))
            return null;

        $carrier = strtolower($carrier ?? '');

        if (str_contains($carrier, 'aramex')) {
            return "https://www.aramex.com/ae/en/track/results?source=aramex&ShipmentNumber=" . urlencode($number);
        } elseif (str_contains($carrier, 'usps')) {
            return "https://tools.usps.com/go/TrackConfirmAction?tLabels=" . urlencode($number);
        } elseif (str_contains($carrier, 'dhl')) {
            return "https://www.dhl.com/en/express/tracking.html?AWB=" . urlencode($number);
        } elseif (str_contains($carrier, 'fedex')) {
            return "https://www.fedex.com/fedextrack/?tracknumbers=" . urlencode($number);
        } elseif (str_contains($carrier, 'ups')) {
            return "https://www.ups.com/track?tracknum=" . urlencode($number);
        } elseif (str_contains($carrier, 'india post')) {
            return "https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx?strTrackId=" . urlencode($number);
        } elseif (str_contains($carrier, 'ems')) {
            return "https://www.ems.post/en/tracking?id=" . urlencode($number);
        }

        // Fallback to ParcelsApp for global tracking
        return "https://parcelsapp.com/en/tracking/" . urlencode($number);
    }

    /**
     * Call ParcelsApp API (Global Parcel Tracking)
     */
    private function fetchFromParcelsApp($number, $apiKey, $carrierCode = null)
    {
        try {
            // Step 1: Initiate Tracking Request
            $shipment = ['trackingId' => $number];

            // ParcelsApp v3 requires destinationCountry for some carriers
            // 'Auto' is supported for automatic detection
            $shipment['destinationCountry'] = 'Auto';

            $initPayload = [
                'shipments' => [
                    $shipment
                ],
                'language' => 'en',
                'apiKey' => $apiKey
            ];

            $initResponse = Http::timeout(15)->post('https://parcelsapp.com/api/v3/shipments/tracking', $initPayload);

            Log::info("ParcelsApp Init Response for {$number}: " . $initResponse->body());

            if ($initResponse->failed()) {
                Log::error("ParcelsApp Init Failed: " . $initResponse->body());
                return [
                    'success' => false,
                    'message' => 'Tracking API Initiation Error: ' . $initResponse->status()
                ];
            }

            $initBody = $initResponse->json();

            // ParcelsApp may return data immediately (fromCache) or require polling via UUID
            $trackData = null;

            if (($initBody['done'] ?? false) === true && !empty($initBody['shipments'])) {
                // Cached response — data is available immediately
                $trackData = $initBody['shipments'][0] ?? null;
                Log::info("ParcelsApp returned cached data for {$number}");
            } else {
                // Async response — poll using UUID
                $uuid = $initBody['uuid'] ?? null;
                if (!$uuid) {
                    return [
                        'success' => false,
                        'message' => 'Failed to obtain tracking UUID from ParcelsApp.'
                    ];
                }

                // Step 2: Poll for Results
                $maxRetries = 5;
                $retryDelay = 2; // seconds

                for ($i = 0; $i < $maxRetries; $i++) {
                    sleep($retryDelay);

                    $getResponse = Http::timeout(15)->get("https://parcelsapp.com/api/v3/shipments/tracking", [
                        'uuid' => $uuid,
                        'apiKey' => $apiKey
                    ]);

                    if ($getResponse->successful()) {
                        $body = $getResponse->json();
                        if (($body['done'] ?? false) === true) {
                            $trackData = $body['shipments'][0] ?? null;
                            break;
                        }
                    } else {
                        Log::warning("ParcelsApp Poll Attempt {$i} failed: " . $getResponse->status());
                    }
                }
            }

            if (!$trackData) {
                return [
                    'success' => false,
                    'message' => 'Tracking data is still being processed. Please try again in a moment.'
                ];
            }

            // --- Parse the actual ParcelsApp v3 response format ---

            // Status: ParcelsApp returns a string status field (e.g., "delivered", "transit", "pickup")
            // It may also return a numeric status_code on some endpoints
            $readableStatus = $this->parseParcelsAppStatus($trackData);

            // Carrier: detected from detectedCarrier object, services array, or carriers array
            $carrierName = $trackData['detectedCarrier']['name']
                ?? $trackData['services'][0]['name']
                ?? $trackData['carriers'][0]
                ?? $trackData['carrier_code']
                ?? 'Unknown';

            // Events: ParcelsApp v3 uses "states" array (not "events")
            // Each state has: date, status, location (optional), carrier
            $states = $trackData['states'] ?? $trackData['events'] ?? [];

            $history = [];
            foreach ($states as $checkpoint) {
                $dateStr = $checkpoint['date'] ?? now()->toIso8601String();
                try {
                    $dateFormatted = Carbon::parse($dateStr)->format('d M Y, h:i A');
                } catch (\Exception $e) {
                    $dateFormatted = $dateStr;
                }

                $history[] = [
                    'date' => $dateFormatted,
                    'status' => $checkpoint['status'] ?? $checkpoint['event'] ?? $readableStatus,
                    'location' => $checkpoint['location'] ?? '',
                    'description' => $checkpoint['additional'] ?? $checkpoint['status'] ?? ''
                ];
            }

            // If no states, use lastState as fallback
            if (empty($history)) {
                $lastState = $trackData['lastState'] ?? null;
                $history[] = [
                    'date' => $lastState
                        ? Carbon::parse($lastState['date'])->format('d M Y, h:i A')
                        : now()->format('d M Y, h:i A'),
                    'status' => $lastState['status'] ?? $readableStatus,
                    'location' => $lastState['location'] ?? '',
                    'description' => $trackData['description'] ?? 'Tracking initialized.'
                ];
            }

            // Sort history descending (newest first)
            usort($history, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            $readableStatus = $this->promoteStatusFromEvents($readableStatus, $history);

            return [
                'success' => true,
                'status' => $readableStatus,
                'history' => $history,
                'carrier' => $carrierName
            ];

        } catch (\Exception $e) {
            Log::error("ParcelsApp Sync Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse the tracking status from ParcelsApp response.
     * Handles both string-based status (v3 actual) and numeric status_code (documented).
     */
    private function parseParcelsAppStatus(array $trackData): string
    {
        // Primary: check string "status" field (actual API response format)
        $statusString = strtolower(trim($trackData['status'] ?? ''));

        $stringStatusMap = [
            'delivered' => 'Delivered',
            'transit' => 'In transit',
            'in_transit' => 'In transit',
            'in transit' => 'In transit',
            'pickup' => 'Pick up',
            'pick_up' => 'Pick up',
            'out_for_delivery' => 'Out for delivery',
            'outfordelivery' => 'Out for delivery',
            'out for delivery' => 'Out for delivery',
            'not_found' => 'Not found',
            'notfound' => 'Not found',
            'exception' => 'Exception',
            'expired' => 'Expired',
            'info_received' => 'Info received',
            'inforeceived' => 'Info received',
            'failed' => 'Failed attempt',
        ];

        if (!empty($statusString) && isset($stringStatusMap[$statusString])) {
            return $stringStatusMap[$statusString];
        }

        // Fallback: check numeric status_code (some API versions)
        $statusCode = $trackData['status_code'] ?? null;
        if ($statusCode !== null) {
            $numericStatusMap = [
                0 => 'Delivered',
                1 => 'Delivered',       // Frozen — no updates, effectively delivered
                2 => 'In transit',
                3 => 'Pick up',
                4 => 'Out for delivery',
                5 => 'Not found',
                6 => 'Failed attempt',
                7 => 'Exception',
                8 => 'Info received',
            ];

            if (isset($numericStatusMap[$statusCode])) {
                return $numericStatusMap[$statusCode];
            }
        }

        // Last resort: try description, then capitalize status string, then Unknown
        if (!empty($trackData['description'])) {
            return $trackData['description'];
        }

        if (!empty($statusString)) {
            return ucfirst($statusString);
        }

        return 'Unknown';
    }


    /**
     * Legacy URL extraction (Fallback)
     */
    private function extractTrackingDetails($url)
    {
        $number = '';
        $carrier = '';

        // Basic parsing logic
        if (str_contains($url, 'aramex')) {
            $carrier = 'aramex';
            // Try to regex number
            if (preg_match('/ShipmentNumber=([0-9]+)/i', $url, $m)) {
                $number = $m[1];
            }
        } elseif (str_contains($url, 'dhl')) {
            $carrier = 'dhl';
            if (preg_match('/AWB=([0-9]+)/i', $url, $m)) {
                $number = $m[1];
            }
        } elseif (str_contains($url, 'fedex')) {
            $carrier = 'fedex';
            if (preg_match('/tracknumbers=([0-9]+)/i', $url, $m)) {
                $number = $m[1];
            }
        }

        return ['number' => $number, 'carrier' => $carrier];
    }

    private function captureTrackingSnapshot(Order $order): array
    {
        return [
            'shipping_company_name' => $order->shipping_company_name,
            'tracking_number' => $order->tracking_number,
            'tracking_status' => $order->tracking_status,
            'tracking_history' => $order->tracking_history,
            'last_tracker_sync' => optional($order->last_tracker_sync)->toIso8601String(),
        ];
    }

    private function logTrackingAuditChanges(Order $order, array $oldSnapshot, string $event): void
    {
        $fieldLabels = [
            'shipping_company_name' => 'Shipping Company',
            'tracking_number' => 'Tracking Number',
            'tracking_status' => 'Tracking Status',
            'tracking_history' => 'Tracking History',
            'last_tracker_sync' => 'Last Tracker Sync',
        ];

        $oldValues = [];
        $newValues = [];

        foreach ($fieldLabels as $field => $label) {
            $oldValue = $oldSnapshot[$field] ?? null;
            $newValue = $field === 'last_tracker_sync'
                ? optional($order->last_tracker_sync)->toIso8601String()
                : $order->{$field};

            $oldCompare = is_array($oldValue) ? json_encode($oldValue) : (string) $oldValue;
            $newCompare = is_array($newValue) ? json_encode($newValue) : (string) $newValue;

            if ($oldCompare !== $newCompare) {
                $oldValues[$label] = $oldValue;
                $newValues[$label] = $newValue;
            }
        }

        if (!empty($oldValues) || !empty($newValues)) {
            AuditLogger::log($event, $order, null, $oldValues, $newValues);
        }
    }
    private function promoteStatusFromEvents(string $currentStatus, array $history): string
    {
        // Status hierarchy — higher index = more advanced stage
        $hierarchy = [
            'Not found' => 0,
            'Info received' => 1,
            'Pick up' => 2,
            'In transit' => 3,
            'Out for delivery' => 4,
            'Delivered' => 5,
        ];

        $currentRank = $hierarchy[$currentStatus] ?? -1;

        // Keywords that indicate a specific status when found in event text
        $promotionKeywords = [
            'Out for delivery' => [
                'out for delivery',
                'out_for_delivery',
                'outfordelivery',
                'out for del',
                'delivering',
                'with delivery courier',
                'out for del.',
                'on vehicle for delivery',
            ],
            'Delivered' => [
                'delivered',
                'shipment delivered',
                'successfully delivered',
                'delivery confirmed',
                'received by',
            ],
        ];

        // Only scan the latest 5 events (history is newest-first)
        $recentEvents = array_slice($history, 0, 5);

        $promotedStatus = $currentStatus;
        $promotedRank = $currentRank;

        foreach ($recentEvents as $event) {
            $combined = strtolower(
                ($event['status'] ?? '') . ' ' . ($event['description'] ?? '')
            );

            foreach ($promotionKeywords as $targetStatus => $keywords) {
                $targetRank = $hierarchy[$targetStatus] ?? -1;
                if ($targetRank <= $promotedRank) {
                    continue; // Only promote forward
                }

                foreach ($keywords as $kw) {
                    if (str_contains($combined, $kw)) {
                        $promotedStatus = $targetStatus;
                        $promotedRank = $targetRank;
                        break 2; // Found higher status, check next event
                    }
                }
            }
        }

        if ($promotedStatus !== $currentStatus) {
            Log::info("Tracking status promoted from '{$currentStatus}' to '{$promotedStatus}' based on event scan");
        }

        return $promotedStatus;
    }
}
