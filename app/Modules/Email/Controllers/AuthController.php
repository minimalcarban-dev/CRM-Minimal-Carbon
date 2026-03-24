<?php

namespace App\Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Services\GmailAuthService;
use App\Modules\Email\Services\GmailSyncService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private GmailAuthService $authService;
    private GmailSyncService $syncService;

    public function __construct(GmailAuthService $authService, GmailSyncService $syncService)
    {
        $this->authService = $authService;
        $this->syncService = $syncService;
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirect(Request $request, int $companyId)
    {
        Company::query()->findOrFail($companyId);

        $adminId = Auth::guard('admin')->id();
        $nonce = Str::random(40);

        $state = Crypt::encryptString(json_encode([
            'admin_id' => $adminId,
            'company_id' => $companyId,
            'nonce' => $nonce,
            'timestamp' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));

        session([
            'oauth_company_id' => $companyId,
            'oauth_admin_id' => $adminId,
            'oauth_nonce' => $nonce,
        ]);

        return redirect($this->authService->getAuthUrl($state));
    }

    /**
     * Handle OAuth callback from Google.
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');
        $state = $request->get('state');

        if ($error || !$code || !$state) {
            return $this->redirectToAccountsWithError('OAuth failed: ' . ($error ?: 'Invalid state'));
        }

        try {
            $stateData = json_decode(Crypt::decryptString($state), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return $this->redirectToAccountsWithError('OAuth failed: Invalid state');
        }

        $companyId = (int) ($stateData['company_id'] ?? 0);
        $stateAdminId = (int) ($stateData['admin_id'] ?? 0);
        $stateNonce = (string) ($stateData['nonce'] ?? '');
        $sessionCompanyId = (int) session('oauth_company_id');
        $sessionAdminId = (int) session('oauth_admin_id');
        $sessionNonce = (string) session('oauth_nonce');
        $currentAdminId = (int) Auth::guard('admin')->id();

        if (
            $companyId < 1 ||
            $stateAdminId < 1 ||
            $stateNonce === '' ||
            $companyId !== $sessionCompanyId ||
            $stateAdminId !== $sessionAdminId ||
            $stateAdminId !== $currentAdminId ||
            !hash_equals($sessionNonce, $stateNonce) ||
            !Company::query()->whereKey($companyId)->exists()
        ) {
            $this->clearOauthSession();

            return $this->redirectToAccountsWithError('OAuth failed: Invalid state');
        }

        try {
            $account = $this->authService->handleCallback(
                $code,
                $companyId,
                $currentAdminId
            );

            $message = "Account {$account->email_address} connected successfully!";

            try {
                $stats = $this->syncService->sync($account, (int) config('gmail.sync.initial_limit', 20));
                $message .= " Initial sync completed: {$stats['added']} added, {$stats['updated']} updated.";
            } catch (\Throwable $syncException) {
                $message .= ' Account connected, but initial sync failed. You can run a manual sync from the inbox.';
            }

            $this->clearOauthSession();

            return redirect()->route('email.inbox', $account->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            $this->clearOauthSession();

            return $this->redirectToAccountsWithError('Failed to connect: ' . $e->getMessage());
        }
    }


    /**
     * Disconnect/Revoke an account.
     */
    public function revoke(EmailAccount $account)
    {
        $this->authorize('deleteAccount', $account);

        $this->authService->revokeAccess($account);

        return redirect()->route('email.accounts.list')
            ->with('success', 'Account disconnected.');
    }

    /**
     * Remove an account.
     */
    public function destroy(EmailAccount $account)
    {
        $this->authorize('deleteAccount', $account);

        // Check if account is still connected (has tokens)
        if ($account->access_token || $account->refresh_token) {
            return redirect()->back()->with('error', 'This account is still active. Please disconnect the account first before attempting to remove it.');
        }


        $account->delete();

        return redirect()->route('email.accounts.list')
            ->with('success', 'Account removed successfully.');
    }


    private function clearOauthSession(): void
    {
        session()->forget([
            'oauth_company_id',
            'oauth_admin_id',
            'oauth_nonce',
        ]);
    }

    private function redirectToAccountsWithError(string $message): RedirectResponse
    {
        return redirect()->route('email.accounts.list')->with('error', $message);
    }
}
