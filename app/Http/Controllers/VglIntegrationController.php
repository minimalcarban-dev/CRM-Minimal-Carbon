<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Diamond;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VglIntegrationController extends Controller
{
    /**
     * Push order data to VGL backend for certificate creation.
     *
     * Transforms CRM field names into VGL-expected field names:
     *   - client_name      → certifier_name
     *   - order_type        → type (value-mapped)
     *   - gross_sell         → value
     *   - gold_detail.name  → metal_purity
     *   - images[0]         → image_url (preview)
     *   - Diamond.color     → diamond_color
     *   - Diamond.clarity   → diamond_clarity
     *   - Diamond.weight    → diamond_weight
     *   - Diamond.shape     → diamond_shape
     *   - Diamond.measurement → diamond_measurement
     */
    public function pushOrder(Order $order)
    {
        // ─── C2 FIX: Duplicate push guard ──────────────────────────
        if ($order->vgl_pushed_at && $order->vgl_push_status === 'success') {
            return back()->with('warning', 'This order was already sent to VGL on ' . $order->vgl_pushed_at->format('d M Y, h:i A') . '. To re-send, please contact admin.');
        }

        $order->load(['company', 'goldDetail']);

        // ─── Extract linked Diamond data ───────────────────────────
        $diamondData = [];
        $skus = $order->diamond_skus ?? ($order->diamond_sku ? [$order->diamond_sku] : []);

        if (!empty($skus)) {
            $diamond = Diamond::where('sku', $skus[0])->first();
            if ($diamond) {
                $diamondData = [
                    'diamond_color' => $diamond->color,
                    'diamond_clarity' => $diamond->clarity,
                    'diamond_weight' => $diamond->weight,
                    'diamond_shape' => $diamond->shape,
                    'diamond_cut' => $diamond->cut,
                    'diamond_measurement' => $diamond->measurement,
                ];
            }
        }

        // ─── Order Type Mapping ────────────────────────────────────
        // CRM: ready_to_ship, custom_diamond, custom_jewellery
        // VGL: diamond, jewellery
        $vglType = match ($order->order_type) {
            'custom_diamond' => 'diamond',
            'custom_jewellery' => 'jewellery',
            'ready_to_ship' => 'jewellery',
            default => 'jewellery',
        };

        // ─── Build Transformed Payload ─────────────────────────────
        $payload = [
            // Mapped fields (CRM name → VGL name)
            'crm_order_id' => $order->id,
            'certifier_name' => $order->client_name,                    // client_name → certifier_name
            'type' => $vglType,                                // order_type → type
            'value' => $order->gross_sell,                       // gross_sell → value
            'metal_purity' => optional($order->goldDetail)->name,      // gold_detail_id → metal_purity
            'image_url' => $this->getFirstValidImageUrl($order),
            'order_date' => $order->created_at->toDateString(),       // created_at → date
            'title' => $order->jewellery_details
                ?? $order->diamond_details
                ?? 'Order #' . $order->id,
            'item' => $order->product_other
                ?? $order->jewellery_details
                ?? '',

            // Diamond data from linked Diamond record
            ...$diamondData,

            // Reference data (displayed in VGL but won't auto-fill certificate form)
            // Reference data — VGL validates 'client_email' as 'email' format,
            // so we must NOT send masked values. Send null to avoid validation failure.
            'client_email'   => null,
            'client_mobile'  => null,
            'diamond_skus' => $skus,
            'company_name' => optional($order->company)->name,
            'special_notes' => $order->special_notes,
            // M3 FIX: Limit images array to max 10
            'images' => array_slice($this->getAllValidImageUrls($order), 0, 10),
        ];

        // ─── Send to VGL Backend ───────────────────────────────────
        try {
            $response = Http::retry(3, 500)                                   // M1 FIX: Retry 3 times with 500ms gap
                ->connectTimeout(5)                                            // M5 FIX: Separate connect timeout
                ->timeout(15)
                ->asJson()
                ->withHeaders([
                    'X-API-Key' => config('services.vgl.api_key'),
                    'X-Idempotency-Key' => 'crm-order-' . $order->id . '-' . $order->updated_at->timestamp, // M2 FIX
                ])
                ->post(config('services.vgl.base_url') . '/api/external/orders', $payload);

            if ($response->successful()) {
                $vglData = $response->json();

                // ─── C2+H2 FIX: Track push state on order ─────────
                $order->update([
                    'vgl_pushed_at' => now(),
                    'vgl_push_status' => 'success',
                    'vgl_crm_order_id' => $vglData['id'] ?? null,
                ]);

                Log::info('Order pushed to VGL successfully', [
                    'order_id' => $order->id,
                    'vgl_response' => $vglData,
                ]);

                // ─── M4 FIX: Audit log entry ──────────────────────
                $this->logVglPush($order, 'success', $vglData ?? []);

                return back()->with('success', 'Order data sent to VGL successfully! 🚀');
            }

            // ─── Failed push — track it ───────────────────────────
            $order->update([
                'vgl_push_status' => 'failed',
            ]);

            Log::warning('VGL rejected order push', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $this->logVglPush($order, 'failed', ['status' => $response->status()]);

            // H5 FIX: Don't expose raw response body to user
            return back()->with('error', 'VGL push failed. Status: ' . $response->status() . '. Please contact admin if this persists.');
        } catch (\Exception $e) {
            $order->update([
                'vgl_push_status' => 'failed',
            ]);

            Log::error('VGL push failed with exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $this->logVglPush($order, 'error', ['exception' => $e->getMessage()]);

            return back()->with('error', 'Could not connect to VGL server. Please check if VGL is running and try again.');
        }
    }

    /**
     * Log VGL push action to the audit log.
     * M4 FIX: Ensures VGL pushes appear in the order edit history timeline.
     */
    private function logVglPush(Order $order, string $result, array $details = []): void
    {
        try {
            AuditLog::create([
                'auditable_type' => Order::class,
                'auditable_id'   => $order->id,
                'user_id'        => auth('admin')->id(),
                'event'          => 'vgl_push_' . $result,
                'old_values'     => [],
                'new_values'     => [
                    'vgl_result' => $result,
                    'details'    => $details,
                ],
                'created_at'     => now(),
            ]);
        } catch (\Exception $e) {
            // Don't let audit log failure break the main flow
            Log::warning('Failed to create VGL push audit log', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get the first valid URL from order images.
     *
     * Handles multiple storage formats:
     *  - Plain URL strings: ["https://..."]
     *  - Object with url key: [{"url": "https://...", "public_id": "..."}]
     *  - Double-encoded JSON string (needs extra json_decode)
     */
    private function getFirstValidImageUrl(Order $order): ?string
    {
        $images = $order->images;

        // Handle double-encoded JSON (string instead of array)
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (empty($images) || !is_array($images)) {
            return null;
        }

        foreach ($images as $image) {
            $url = null;

            // Object format: {"url": "https://...", "public_id": "..."}
            if (is_array($image) && isset($image['url'])) {
                $url = $image['url'];
            }
            // Plain string format: "https://..."
            elseif (is_string($image)) {
                $url = $image;
            }

            if ($url && $this->isValidExternalUrl($url)) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Get all valid image URLs from order images.
     */
    private function getAllValidImageUrls(Order $order): array
    {
        $images = $order->images;

        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (empty($images) || !is_array($images)) {
            return [];
        }

        $urls = [];
        foreach ($images as $image) {
            $url = null;
            if (is_array($image) && isset($image['url'])) {
                $url = $image['url'];
            } elseif (is_string($image)) {
                $url = $image;
            }

            if ($url && $this->isValidExternalUrl($url)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /**
     * Validate that a URL is a safe external URL (not internal/SSRF).
     * M6 FIX: Prevents file://, ftp://, localhost, and private IP SSRF attacks.
     */
    private function isValidExternalUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');

        // Only allow http/https schemes
        if (!in_array($scheme, ['http', 'https'])) {
            return false;
        }

        // Block localhost, loopback, and common internal hosts
        $blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1', 'metadata.google.internal'];
        if (in_array($host, $blockedHosts)) {
            return false;
        }

        // Block private IP ranges (169.254.x.x, 10.x.x.x, 172.16-31.x.x, 192.168.x.x)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }
}
