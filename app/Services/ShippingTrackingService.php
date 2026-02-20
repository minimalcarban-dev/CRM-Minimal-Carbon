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

        // 2. Fetch from 17Track
        $apiKey = env('17TRACK_API_KEY', '016E049ACA4113B2A846E1FD39D403C3');
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => '17Track API Key is missing.'
            ];
        }

        $trackingData = $this->fetchFrom17Track($trackingNumber, $apiKey);

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
     * Map company name to TrackingMore courier code
     */
    private function detectCarrierCode($companyName)
    {
        if (empty($companyName))
            return null;

        $name = strtolower(trim($companyName));

        if (str_contains($name, 'aramex'))
            return 'aramex';
        if (str_contains($name, 'dhl'))
            return 'dhl';
        if (str_contains($name, 'fedex'))
            return 'fedex';
        if (str_contains($name, 'ups'))
            return 'ups';
        if (str_contains($name, 'usps'))
            return 'usps';
        if (str_contains($name, 'india post') || str_contains($name, 'speed post'))
            return 'india-post';

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
            return "https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx";
        } elseif (str_contains($carrier, 'ems')) {
            return "https://www.ems.post/en/tracking?id=" . urlencode($number);
        }

        return null;
    }

    /**
     * Call 17Track API
     */
    private function fetchFrom17Track($number, $apiKey)
    {
        try {
            $postPayload = [
                ['number' => $number]
            ];

            // Step 1: Register Tracking
            $registerResponse = Http::withHeaders([
                '17token' => $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.17track.net/track/v2.2/register', $postPayload);

            Log::info("17Track Register Response for {$number}: " . $registerResponse->body());

            // Step 2: Get Tracking Details
            $getResponse = Http::withHeaders([
                '17token' => $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.17track.net/track/v2.2/gettrackinfo', $postPayload);

            if ($getResponse->failed()) {
                Log::error("17Track Get Failed: " . $getResponse->body());
                return [
                    'success' => false,
                    'message' => 'Tracking API Error: ' . $getResponse->status()
                ];
            }

            $body = $getResponse->json();

            if (empty($body['data']['accepted'][0]['track_info'])) {
                $errorMsg = $body['data']['rejected'][0]['error']['message'] ?? 'No tracking data returned from API.';
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }

            $trackInfo = $body['data']['accepted'][0]['track_info'];
            $latestStatus = $trackInfo['latest_status']['status'] ?? 'Unknown';

            // 17Track statuses mapping
            $statusMap = [
                'NotFound' => 'Not found',
                'InfoReceived' => 'Info received',
                'InTransit' => 'In transit',
                'Expired' => 'Expired',
                'AvailableForPickup' => 'Pick up',
                'OutForDelivery' => 'Out for delivery',
                'Undelivered' => 'Undelivered',
                'Delivered' => 'Delivered',
                'Alert' => 'Alert',
                'Exception' => 'Exception',
            ];
            $readableStatus = $statusMap[$latestStatus] ?? $latestStatus;

            $providerName = $trackInfo['tracking']['providers'][0]['provider']['name'] ?? null;
            $events = $trackInfo['tracking']['providers'][0]['events'] ?? [];

            $history = [];
            foreach ($events as $checkpoint) {
                $dateStr = $checkpoint['time_iso'] ?? $checkpoint['time_utc'] ?? now();
                try {
                    $dateFormatted = Carbon::parse($dateStr)->format('d M Y, h:i A');
                } catch (\Exception $e) {
                    $dateFormatted = $dateStr;
                }

                $history[] = [
                    'date' => $dateFormatted,
                    'status' => $checkpoint['stage'] ?? $readableStatus,
                    'location' => $checkpoint['location'] ?? '',
                    'description' => $checkpoint['description'] ?? ''
                ];
            }

            if (empty($history)) {
                $history[] = [
                    'date' => now()->format('d M Y, h:i A'),
                    'status' => $readableStatus,
                    'location' => '',
                    'description' => 'Tracking initialized. Please check back later.'
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
                'carrier' => $providerName
            ];

        } catch (\Exception $e) {
            Log::error("Tracking Sync Exception: " . $e->getMessage());
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
