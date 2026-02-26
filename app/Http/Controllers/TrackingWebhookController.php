<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrackingWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from 17Track API.
     */
    public function handle17Track(Request $request)
    {
        $payload = $request->all();
        Log::info('17Track Webhook received', [
            'tracking_count' => count($payload['data']['accepted'] ?? $payload['data'] ?? []),
        ]);
        $data = $payload['data'] ?? $payload;

        // 17Track pushes data usually wrapped in "data" -> "accepted" array, or a direct array
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
                    $dateFormatted = now()->format('d M Y, h:i A');
                } else {
                    try {
                        $dateFormatted = Carbon::parse($dateStr)->format('d M Y, h:i A');
                    } catch (\Exception $e) {
                        $dateFormatted = now()->format('d M Y, h:i A');
                    }
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
}
