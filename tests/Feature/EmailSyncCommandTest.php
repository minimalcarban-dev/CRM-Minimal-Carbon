<?php

use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Services\AuditLogger;
use App\Modules\Email\Services\GmailAuthService;
use App\Modules\Email\Services\GmailSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('resolves fresh gmail services from the container', function () {
    $firstAuthService = app(GmailAuthService::class);
    $secondAuthService = app(GmailAuthService::class);
    $firstSyncService = app(GmailSyncService::class);
    $secondSyncService = app(GmailSyncService::class);

    expect(spl_object_id($firstAuthService))->not->toBe(spl_object_id($secondAuthService))
        ->and(spl_object_id($firstSyncService))->not->toBe(spl_object_id($secondSyncService));
});

it('sync command only processes active accounts', function () {
    $activeAccount = EmailAccount::create([
        'email_address' => 'active@gmail.com',
        'provider' => 'gmail',
        'is_active' => true,
    ]);

    EmailAccount::create([
        'email_address' => 'inactive@gmail.com',
        'provider' => 'gmail',
        'is_active' => false,
    ]);

    $syncService = Mockery::mock(GmailSyncService::class);
    $syncService->shouldReceive('sync')
        ->once()
        ->withArgs(fn (EmailAccount $account, int $limit) => $account->is($activeAccount) && $limit === 7)
        ->andReturn(['added' => 3, 'updated' => 0, 'failed' => 0, 'deleted' => 0]);
    app()->instance(GmailSyncService::class, $syncService);

    $logger = Mockery::mock(AuditLogger::class);
    $logger->shouldReceive('logSync')
        ->once()
        ->withArgs(fn (EmailAccount $account, string $status, array $details) => $account->is($activeAccount) && $status === 'success' && $details['added'] === 3);
    app()->instance(AuditLogger::class, $logger);

    $this->artisan('email:sync --limit=7')
        ->assertExitCode(0);
});
