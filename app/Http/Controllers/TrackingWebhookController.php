<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrackingWebhookController extends Controller
{
    public function handle17Track(Request $request)
    {
        $payload = $request->all();
        Log::info('17Track Webhook received', [
            'tracking_count' => count(
                is_array($payload['data']['accepted'] ?? null)
                ? $payload['data']['accepted']
                : (is_array($payload['data'] ?? null) ? $payload['data'] : [])
            ),
        ]);
        $data = $payload['data'] ?? $payload;

        $trackings = [];
        if (isset($data['accepted']) && is_array($data['accepted'])) {
            $trackings = $data['accepted'];
        } elseif (isset($data['number'])) {
            $trackings = [$data];
        } elseif (is_array($data) && isset($data[0]['number'])) {
            $trackings = $data;
        } else {
            return response()->json(['success' => true]);
        }

        foreach ($trackings as $track) {
            $number = $track['number'] ?? null;
            if (!$number)
                continue;

            $trackInfo = $track['track_info'] ?? null;
            if (!$trackInfo)
                continue;

            $latestStatus = $trackInfo['latest_status']['status'] ?? 'Unknown';

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
                $dateStr = $checkpoint['time_iso'] ?? $checkpoint['time_utc'] ?? null;
                if ($dateStr === null) {
                    Log::warning('17Track checkpoint missing timestamp', [
                        'tracking_number' => $number,
                        'checkpoint' => $checkpoint['description'] ?? 'unknown'
                    ]);
                    continue;
                }

                try {
                    $dateFormatted = Carbon::parse($dateStr)->format('d M Y, h:i A');
                } catch (\Exception $e) {
                    Log::warning('17Track checkpoint timestamp parse failed', [
                        'tracking_number' => $number,
                        'date_string' => $dateStr,
                        'error' => $e->getMessage()
                    ]);
                    continue;
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
                    'description' => 'Tracking update received.'
                ];
            }

            // Sort history descending (newest first)
            usort($history, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Update matching orders
            $orders = Order::where('tracking_number', $number)->get();
            foreach ($orders as $order) {
                $updatedData = [
                    'tracking_status' => $readableStatus,
                    'tracking_history' => $history,
                    'last_tracker_sync' => now(),
                ];

                if (empty($order->shipping_company_name) && !empty($providerName)) {
                    $updatedData['shipping_company_name'] = $providerName;
                }

                $order->update($updatedData);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle ParcelsApp (Global Parcel Tracking) Webhook
     */
    public function handleParcelsApp(Request $request)
    {
        $payload = $request->all();
        Log::info('ParcelsApp Webhook received', ['uuid' => $payload['uuid'] ?? 'unknown']);

        $shipments = $payload['shipments'] ?? [];
        if (empty($shipments)) {
            return response()->json(['success' => true, 'message' => 'No shipments in payload']);
        }

        foreach ($shipments as $trackData) {
            $number = $trackData['trackingId'] ?? null;
            if (!$number)
                continue;

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

            $carrierName = $trackData['carrier_code'] ?? null;
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
                    'description' => 'Tracking update received.'
                ];
            }

            // Sort history descending (newest first)
            usort($history, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Update matching orders
            $orders = Order::where('tracking_number', $number)->get();
            foreach ($orders as $order) {
                $updatedData = [
                    'tracking_status' => $readableStatus,
                    'tracking_history' => $history,
                    'last_tracker_sync' => now(),
                ];

                if (empty($order->shipping_company_name) && !empty($carrierName)) {
                    $updatedData['shipping_company_name'] = $carrierName;
                }

                $order->update($updatedData);
            }
        }

        return response()->json(['success' => true]);
    }
}
