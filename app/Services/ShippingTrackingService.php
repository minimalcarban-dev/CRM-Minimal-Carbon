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
            $order->update([
                'tracking_status' => $trackingData['status'],
                'tracking_history' => $trackingData['history'],
                'last_tracker_sync' => now(),
                'tracking_number' => $trackingNumber, // Ensure number is saved if extracted
                'shipping_company_name' => $order->shipping_company_name ?: ($trackingData['carrier'] ?? 'Unknown Carrier'),
            ]);

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

            $uuid = $initResponse->json()['uuid'] ?? null;
            if (!$uuid) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain tracking UUID from ParcelsApp.'
                ];
            }

            // Step 2: Poll for Results (Short polling since it's a synchronous UI action)
            $maxRetries = 5;
            $retryDelay = 2; // seconds
            $trackData = null;

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

            if (!$trackData) {
                return [
                    'success' => false,
                    'message' => 'Tracking data is still being processed. Please try again in a moment.'
                ];
            }

            $statusCode = $trackData['status_code'] ?? null;

            // ParcelsApp statuses mapping
            $statusMap = [
                0 => 'Delivered',
                2 => 'In transit',
                3 => 'Pick up',
                4 => 'Out for delivery',
                7 => 'Exception',
                8 => 'Info received',
            ];
            $readableStatus = $statusMap[$statusCode] ?? ($trackData['description'] ?? 'In transit');

            $carrierName = $trackData['carrier_code'] ?? 'Unknown';
            $events = $trackData['events'] ?? [];

            $history = [];
            foreach ($events as $checkpoint) {
                $dateStr = $checkpoint['date'] ?? now()->toIso8601String();
                try {
                    $dateFormatted = Carbon::parse($dateStr)->format('d M Y, h:i A');
                } catch (\Exception $e) {
                    $dateFormatted = $dateStr;
                }

                $history[] = [
                    'date' => $dateFormatted,
                    'status' => $checkpoint['event'] ?? $readableStatus,
                    'location' => $checkpoint['location'] ?? '',
                    'description' => $checkpoint['additional'] ?? ''
                ];
            }

            if (empty($history)) {
                $history[] = [
                    'date' => now()->format('d M Y, h:i A'),
                    'status' => $readableStatus,
                    'location' => '',
                    'description' => $trackData['description'] ?? 'Tracking initialized.'
                ];
            }

            // Sort history descending (newest first)
            usort($history, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

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
}
