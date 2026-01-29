<?php

namespace App\Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Services\GmailAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private GmailAuthService $authService;

    public function __construct(GmailAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirect(Request $request, int $companyId)
    {
        // Store company in session to retrieve in callback
        session(['oauth_company_id' => $companyId]);
        
        return redirect($this->authService->getAuthUrl());
    }

    /**
     * Handle OAuth callback from Google.
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');
        $companyId = session('oauth_company_id');

        if ($error || !$code || !$companyId) {
            return redirect()->route('email.accounts.list')
                ->with('error', 'OAuth failed: ' . ($error ?: 'Invalid state'));
        }

        try {
            $account = $this->authService->handleCallback(
                $code,
                $companyId,
                Auth::guard('admin')->id()
            );

            return redirect()->route('email.inbox', $account->id)
                ->with('success', "Account {$account->email_address} connected successfully!");
        } catch (\Exception $e) {
            return redirect()->route('email.accounts.list')
                ->with('error', 'Failed to connect: ' . $e->getMessage());
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
}
