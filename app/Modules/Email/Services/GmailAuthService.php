<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\EmailAccount;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Oauth2;
use Illuminate\Support\Facades\Crypt;

class GmailAuthService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
        
        // Fix for SSL certificate issue on Windows
        $caPath = 'C:\tools\php84\cacert.pem';
        $httpClientOptions = [
            'timeout' => 60.0,        // Request timeout (60 seconds)
            'connect_timeout' => 30.0, // Connection timeout (30 seconds)
        ];
        if (file_exists($caPath)) {
            $httpClientOptions['verify'] = $caPath;
        }
        $this->client->setHttpClient(new \GuzzleHttp\Client($httpClientOptions));

        $this->client->setClientId(config('gmail.client_id'));
        $this->client->setClientSecret(config('gmail.client_secret'));
        // Redirect URI is set dynamically when needed to avoid early route resolution errors
        
        foreach (config('gmail.scopes') as $scope) {
            $this->client->addScope($scope);
        }

        $this->client->setAccessType(config('gmail.access_type', 'offline'));
        $this->client->setApprovalPrompt(config('gmail.approval_prompt', 'force'));
        $this->client->setPrompt(config('gmail.prompt', 'consent'));
    }

    /**
     * Get the Google Client instance.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the OAuth authorization URL.
     */
    public function getAuthUrl(): string
    {
        $this->client->setRedirectUri(config('gmail.redirect_uri') ?? route('email.oauth.callback'));
        return $this->client->createAuthUrl();
    }

    /**
     * Handle the OAuth callback and return or create the email account.
     */
    public function handleCallback(string $code, int $companyId, int $adminId): EmailAccount
    {
        $this->client->setRedirectUri(config('gmail.redirect_uri') ?? route('email.oauth.callback'));
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['error'])) {
            throw new \Exception('OAuth Error: ' . ($token['error_description'] ?? $token['error']));
        }

        $this->client->setAccessToken($token);

        // Get user info and Gmail profile
        $oauth2 = new Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();
        $emailAddress = $userInfo->email;

        $account = EmailAccount::withTrashed()->updateOrCreate(
            ['email_address' => $emailAddress],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_in' => $token['expires_in'],
                'token_expires_at' => now()->addSeconds($token['expires_in']),
                'is_active' => true,
                'deleted_at' => null,
            ]
        );

        // Log the connection
        app(AuditLogger::class)->log($account, $adminId, 'oauth_connect', [
            'company_id' => $companyId,
            'email' => $emailAddress
        ]);

        // Attach user with role owner
        $account->users()->syncWithoutDetaching([
            $adminId => ['role' => 'owner', 'company_id' => $companyId]
        ]);

        return $account;
    }

    /**
     * Refresh the access token if needed.
     */
    public function refreshToken(EmailAccount $account): string
    {
        if (!$account->refresh_token) {
            throw new \Exception('No refresh token available for account ' . $account->email_address);
        }

        $token = $this->client->fetchAccessTokenWithRefreshToken($account->refresh_token);

        if (isset($token['error'])) {
            $account->update([
                'sync_status' => 'error',
                'sync_error' => 'Token Refresh Failed: ' . ($token['error_description'] ?? $token['error'])
            ]);
            throw new \Exception('Token Refresh Failed: ' . ($token['error_description'] ?? $token['error']));
        }

        $account->update([
            'access_token' => $token['access_token'],
            'expires_in' => $token['expires_in'],
            'token_expires_at' => now()->addSeconds($token['expires_in']),
        ]);

        return $token['access_token'];
    }

    /**
     * Set the access token for the client from an EmailAccount.
     */
    public function setTokenForAccount(EmailAccount $account): void
    {
        if ($account->isTokenExpired()) {
            $this->refreshToken($account);
        }

        $this->client->setAccessToken($account->access_token);
    }

    /**
     * Revoke access for an account.
     */
    public function revokeAccess(EmailAccount $account): bool
    {
        try {
            $this->client->revokeToken($account->refresh_token ?? $account->access_token);
        } catch (\Exception $e) {
            // Ignore revocation errors if token is already invalid
        }

        $account->update([
            'access_token' => null,
            'refresh_token' => null,
            'is_active' => false,
            'sync_status' => 'paused'
        ]);

        return true;
    }
}
