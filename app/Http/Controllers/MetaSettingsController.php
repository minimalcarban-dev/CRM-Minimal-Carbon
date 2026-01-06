<?php

namespace App\Http\Controllers;

use App\Models\MetaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaSettingsController extends Controller
{
    /**
     * Show the Meta integration settings page
     */
    public function index()
    {
        $accounts = MetaAccount::orderBy('created_at', 'desc')->get();

        $webhookUrl = url('/webhook/meta');
        $verifyToken = config('services.meta.webhook_verify_token');

        $isConfigured = !empty(config('services.meta.app_id'))
            && !empty(config('services.meta.app_secret'));

        return view('settings.meta', compact('accounts', 'webhookUrl', 'verifyToken', 'isConfigured'));
    }

    /**
     * Store a new Meta account connection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:facebook,instagram',
            'page_id' => 'required|string|max:255',
            'access_token' => 'required|string',
        ]);

        // Verify the access token with Meta API
        $verification = $this->verifyAccessToken($validated['access_token'], $validated['page_id']);

        if (!$verification['valid']) {
            return back()
                ->withInput()
                ->withErrors(['access_token' => $verification['error'] ?? 'Invalid access token']);
        }

        // Create the account
        MetaAccount::create([
            'name' => $validated['name'],
            'platform' => $validated['platform'],
            'page_id' => $validated['page_id'],
            'account_id' => $verification['account_id'] ?? $validated['page_id'],
            'access_token' => $validated['access_token'], // Will be encrypted by model
            'is_active' => true,
            'token_expires_at' => $verification['expires_at'] ?? null,
        ]);

        return redirect()->route('settings.meta.index')
            ->with('success', 'Meta account connected successfully!');
    }

    /**
     * Toggle account active status
     */
    public function toggle(MetaAccount $account)
    {
        $account->update(['is_active' => !$account->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $account->is_active,
        ]);
    }

    /**
     * Delete a Meta account connection
     */
    public function destroy(MetaAccount $account)
    {
        $account->delete();

        return redirect()->route('settings.meta.index')
            ->with('success', 'Meta account disconnected successfully');
    }

    /**
     * Refresh access token
     */
    public function refresh(MetaAccount $account)
    {
        try {
            $response = Http::get('https://graph.facebook.com/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.meta.app_id'),
                'client_secret' => config('services.meta.app_secret'),
                'fb_exchange_token' => $account->decrypted_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $account->update([
                    'access_token' => $data['access_token'],
                    'token_expires_at' => isset($data['expires_in'])
                        ? now()->addSeconds($data['expires_in'])
                        : null,
                ]);

                return back()->with('success', 'Access token refreshed successfully!');
            }

            return back()->withErrors(['error' => 'Failed to refresh token: ' . ($response->json()['error']['message'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            Log::error('Token refresh failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to refresh token']);
        }
    }

    /**
     * Test webhook connection
     */
    public function testWebhook()
    {
        $webhookUrl = url('/webhook/meta');
        $verifyToken = config('services.meta.webhook_verify_token');

        // Simulate webhook verification request
        try {
            $response = Http::get($webhookUrl, [
                'hub_mode' => 'subscribe',
                'hub_verify_token' => $verifyToken,
                'hub_challenge' => 'test_challenge_123',
            ]);

            if ($response->successful() && $response->body() === 'test_challenge_123') {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook is configured correctly!',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Webhook verification failed. Check your verify token.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not reach webhook endpoint: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify access token with Meta API
     */
    protected function verifyAccessToken(string $token, string $pageId): array
    {
        try {
            // Debug token to check validity
            $response = Http::get("https://graph.facebook.com/debug_token", [
                'input_token' => $token,
                'access_token' => config('services.meta.app_id') . '|' . config('services.meta.app_secret'),
            ]);

            if (!$response->successful()) {
                return ['valid' => false, 'error' => 'Could not verify token with Meta API'];
            }

            $data = $response->json()['data'] ?? [];

            if (!($data['is_valid'] ?? false)) {
                return ['valid' => false, 'error' => $data['error']['message'] ?? 'Token is invalid'];
            }

            // Get page info to verify page_id
            $pageResponse = Http::get("https://graph.facebook.com/{$pageId}", [
                'access_token' => $token,
                'fields' => 'id,name',
            ]);

            if (!$pageResponse->successful()) {
                return ['valid' => false, 'error' => 'Could not access the specified page'];
            }

            $pageData = $pageResponse->json();

            return [
                'valid' => true,
                'account_id' => $pageData['id'] ?? $pageId,
                'account_name' => $pageData['name'] ?? null,
                'expires_at' => isset($data['expires_at']) && $data['expires_at'] > 0
                    ? now()->addSeconds($data['expires_at'] - time())
                    : null,
            ];
        } catch (\Exception $e) {
            Log::error('Token verification failed', ['error' => $e->getMessage()]);
            return ['valid' => false, 'error' => 'Verification request failed'];
        }
    }
}
