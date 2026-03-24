<?php

use App\Models\Admin;
use App\Models\Company;
use App\Models\Permission;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Services\GmailAuthService;
use App\Modules\Email\Services\GmailSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Mockery;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createEmailAdmin(bool $isSuper = false): Admin
{
    static $counter = 1;

    return Admin::create([
        'name' => 'Email Admin ' . $counter,
        'email' => 'email-admin-' . $counter++ . '@example.com',
        'password' => bcrypt('secret123'),
        'phone_number' => '9999999999',
        'is_super' => $isSuper,
    ]);
}

function grantMailAccess(Admin $admin): void
{
    $permission = Permission::firstOrCreate(
        ['slug' => 'mail.access'],
        [
            'name' => 'Access Mail System',
            'category' => 'mail',
            'description' => 'Access the mail system',
        ]
    );

    $admin->permissions()->syncWithoutDetaching([$permission->id]);
    $admin->clearPermissionCache();
}

function createEmailCompany(): Company
{
    static $counter = 1;

    return Company::create([
        'name' => 'Email Company ' . $counter++,
        'status' => 'active',
    ]);
}

it('allows admin with mail access permission to open accounts page', function () {
    $admin = createEmailAdmin();
    grantMailAccess($admin);

    $this->actingAs($admin, 'admin');

    $this->get(route('email.accounts.list'))
        ->assertOk();
});

it('redirects to google oauth with encrypted state', function () {
    $admin = createEmailAdmin();
    $company = createEmailCompany();
    grantMailAccess($admin);

    $capturedState = null;
    $authService = Mockery::mock(GmailAuthService::class);
    $authService->shouldReceive('getAuthUrl')
        ->once()
        ->with(Mockery::type('string'))
        ->andReturnUsing(function (string $state) use (&$capturedState) {
            $capturedState = $state;

            return 'https://google.test/oauth';
        });

    app()->instance(GmailAuthService::class, $authService);

    $this->actingAs($admin, 'admin');

    $this->get(route('email.oauth.redirect', $company->id))
        ->assertRedirect('https://google.test/oauth');

    $this->assertNotNull($capturedState);

    $decoded = json_decode(Crypt::decryptString($capturedState), true, 512, JSON_THROW_ON_ERROR);

    expect($decoded['admin_id'])->toBe($admin->id)
        ->and($decoded['company_id'])->toBe($company->id)
        ->and($decoded['nonce'])->not->toBeEmpty();

    expect(session('oauth_admin_id'))->toBe($admin->id)
        ->and(session('oauth_company_id'))->toBe($company->id)
        ->and(session('oauth_nonce'))->toBe($decoded['nonce']);
});

it('rejects callback requests with invalid oauth state', function () {
    $admin = createEmailAdmin();
    grantMailAccess($admin);

    $this->actingAs($admin, 'admin');

    $this->withSession([
        'oauth_admin_id' => $admin->id,
        'oauth_company_id' => 99,
        'oauth_nonce' => 'known-nonce',
    ])->get(route('email.oauth.callback', [
        'code' => 'oauth-code',
        'state' => 'invalid-state',
    ]))
        ->assertRedirect(route('email.accounts.list'))
        ->assertSessionHas('error');
});

it('connects an account and triggers an initial sync on callback', function () {
    $admin = createEmailAdmin();
    $company = createEmailCompany();
    $account = EmailAccount::create([
        'email_address' => 'owner@gmail.com',
        'provider' => 'gmail',
        'is_active' => true,
    ]);
    grantMailAccess($admin);

    $state = Crypt::encryptString(json_encode([
        'admin_id' => $admin->id,
        'company_id' => $company->id,
        'nonce' => 'valid-nonce',
        'timestamp' => now()->timestamp,
    ], JSON_THROW_ON_ERROR));

    $authService = Mockery::mock(GmailAuthService::class);
    $authService->shouldReceive('handleCallback')
        ->once()
        ->with('oauth-code', $company->id, $admin->id)
        ->andReturn($account);
    app()->instance(GmailAuthService::class, $authService);

    $syncService = Mockery::mock(GmailSyncService::class);
    $syncService->shouldReceive('sync')
        ->once()
        ->withArgs(fn (EmailAccount $syncAccount, int $limit) => $syncAccount->is($account) && $limit === 20)
        ->andReturn(['added' => 2, 'updated' => 1, 'failed' => 0, 'deleted' => 0]);
    app()->instance(GmailSyncService::class, $syncService);

    $this->actingAs($admin, 'admin');

    $this->withSession([
        'oauth_admin_id' => $admin->id,
        'oauth_company_id' => $company->id,
        'oauth_nonce' => 'valid-nonce',
    ])->get(route('email.oauth.callback', [
        'code' => 'oauth-code',
        'state' => $state,
    ]))
        ->assertRedirect(route('email.inbox', $account->id))
        ->assertSessionHas('success');

    expect(session()->has('oauth_nonce'))->toBeFalse();
});

it('shows a clear error when a gmail account is already owned by another user', function () {
    $admin = createEmailAdmin();
    $company = createEmailCompany();
    grantMailAccess($admin);

    $state = Crypt::encryptString(json_encode([
        'admin_id' => $admin->id,
        'company_id' => $company->id,
        'nonce' => 'duplicate-nonce',
        'timestamp' => now()->timestamp,
    ], JSON_THROW_ON_ERROR));

    $authService = Mockery::mock(GmailAuthService::class);
    $authService->shouldReceive('handleCallback')
        ->once()
        ->andThrow(new RuntimeException('This Gmail account is already connected to another user.'));
    app()->instance(GmailAuthService::class, $authService);

    $this->actingAs($admin, 'admin');

    $this->withSession([
        'oauth_admin_id' => $admin->id,
        'oauth_company_id' => $company->id,
        'oauth_nonce' => 'duplicate-nonce',
    ])->get(route('email.oauth.callback', [
        'code' => 'oauth-code',
        'state' => $state,
    ]))
        ->assertRedirect(route('email.accounts.list'))
        ->assertSessionHas('error');
});

it('prevents another admin from opening an owners mailbox', function () {
    $owner = createEmailAdmin();
    $other = createEmailAdmin();
    $company = createEmailCompany();
    $account = EmailAccount::create([
        'email_address' => 'private@gmail.com',
        'provider' => 'gmail',
        'is_active' => true,
    ]);

    $account->users()->attach($owner->id, [
        'role' => 'owner',
        'company_id' => $company->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    grantMailAccess($other);

    $this->actingAs($other, 'admin');

    $this->get(route('email.inbox', $account->id))
        ->assertRedirect(route('admin.dashboard'));
});
