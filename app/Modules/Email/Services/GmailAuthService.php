<?php

namespace App\Modules\Email\Services;

use App\Modules\Email\Models\EmailAccount;
use GuzzleHttp\Client as GuzzleClient;
use Google\Client;
use Google\Service\Oauth2;
use Illuminate\Support\Facades\DB;

class GmailAuthService
{
    private Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client();

        $httpClientOptions = [
            'timeout' => 60.0,
            'connect_timeout' => 30.0,
        ];

        $verify = config('gmail.http.verify');
        if (is_string($verify) && $verify !== '' && file_exists($verify)) {
            $httpClientOptions['verify'] = $verify;
        } elseif (is_bool($verify)) {
            $httpClientOptions['verify'] = $verify;
        }

        $this->client->setHttpClient(new GuzzleClient($httpClientOptions));

        $this->client->setClientId(config('gmail.client_id'));
        $this->client->setClientSecret(config('gmail.client_secret'));

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
    public function getAuthUrl(?string $state = null): string
    {
        $this->setRedirectUri();

        if ($state !== null) {
            $this->client->setState($state);
        }

        return $this->client->createAuthUrl();
    }

    /**
     * Handle the OAuth callback and return or create the email account.
     */
    public function handleCallback(string $code, int $companyId, int $adminId): EmailAccount
    {
        $this->setRedirectUri();
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \Exception('OAuth Error: ' . ($token['error_description'] ?? $token['error']));
        }

        $this->client->setAccessToken($token);

        // Get user info and Gmail profile
        $oauth2 = new Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();
        $emailAddress = $userInfo->email;

        $dataToUpdate = [
            'access_token' => $token['access_token'],
            'expires_in' => $token['expires_in'],
            'token_expires_at' => now()->addSeconds($token['expires_in']),
            'is_active' => true,
            'deleted_at' => null,
            'sync_status' => 'idle', // Reset status on reconnect
            'sync_error' => null,
        ];

        if (isset($token['refresh_token'])) {
            $dataToUpdate['refresh_token'] = $token['refresh_token'];
        }

        $account = DB::transaction(function () use ($adminId, $companyId, $dataToUpdate, $emailAddress) {
            $account = EmailAccount::withTrashed()
                ->with('users:id')
                ->where('email_address', $emailAddress)
                ->first();

            if ($account && $account->users->where('id', '!=', $adminId)->isNotEmpty()) {
                throw new \RuntimeException('This Gmail account is already connected to another user.');
            }

            if (!$account) {
                $account = new EmailAccount([
                    'email_address' => $emailAddress,
                    'provider' => 'gmail',
                ]);
            }

            $account->fill($dataToUpdate);
            $account->provider = 'gmail';
            $account->save();

            if ($account->trashed()) {
                $account->restore();
            }

            $pivotQuery = $account->users()->newPivotStatement()
                ->where('email_account_id', $account->id)
                ->where('user_id', $adminId);

            $pivotQuery->delete();

            $account->users()->attach($adminId, [
                'role' => 'owner',
                'company_id' => $companyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $account->fresh(['users']);
        });

        // Log the connection
        app(AuditLogger::class)->log($account, $adminId, 'oauth_connect', [
            'company_id' => $companyId,
            'email' => $emailAddress,
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

        try {
            $token = $this->client->fetchAccessTokenWithRefreshToken($account->refresh_token);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            // Check for specific OAuth errors
            if (str_contains($errorMsg, 'invalid_grant') || str_contains($errorMsg, 'expired_token')) {
                $account->update([
                    'sync_status' => 'error',
                    'sync_error' => 'Authorization revoked or expired. Please reconnect your account.',
                    'is_active' => false // deactivate to stop retry loop
                ]);
            } else {
                $account->update([
                    'sync_status' => 'error',
                    'sync_error' => 'Token Refresh Failed: ' . $errorMsg
                ]);
            }
            throw $e;
        }

        if (isset($token['error'])) {
            $errorMsg = $token['error_description'] ?? $token['error'];
            $account->update([
                'sync_status' => 'error',
                'sync_error' => 'Token Refresh Failed: ' . $errorMsg
            ]);
            if (str_contains($errorMsg, 'invalid_grant')) {
                $account->update(['is_active' => false]);
            }
            throw new \Exception('Token Refresh Failed: ' . $errorMsg);
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

    private function setRedirectUri(): void
    {
        $this->client->setRedirectUri(config('gmail.redirect_uri') ?: route('email.oauth.callback'));
    }
}
