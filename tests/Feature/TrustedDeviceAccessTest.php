<?php

use App\Models\AllowedIp;
use App\Models\AppSetting;
use App\Models\Admin;
use App\Http\Middleware\IpRestriction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows a trusted browser to keep access after the public ip changes', function () {
    AppSetting::set('ip_restriction_enabled', 'true');

    $token = Str::random(64);

    AllowedIp::create([
        'ip_address' => '10.0.0.10',
        'device_token' => $token,
        'user_agent' => 'TestBrowser/1.0',
        'last_used_at' => now()->subDay(),
        'city' => null,
        'country' => null,
        'label' => 'Test trusted browser',
        'is_active' => true,
        'added_by' => null,
    ]);

    $request = Request::create('/__trusted-device-test', 'GET', [], [
        'device_trust_token' => $token,
    ], [], [
        'REMOTE_ADDR' => '10.0.0.20',
        'HTTP_USER_AGENT' => 'TestBrowser/1.0',
    ]);

    $middleware = app(IpRestriction::class);
    $response = $middleware->handle($request, function () {
        return response('ok');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('blocks a logged in admin when the trusted device token is missing', function () {
    AppSetting::set('ip_restriction_enabled', 'true');

    $admin = Admin::create([
        'name' => 'Blocked Admin',
        'email' => 'blocked@example.com',
        'password' => Hash::make('secret123'),
        'phone_number' => '0000000002',
        'is_super' => true,
    ]);

    Auth::guard('admin')->loginUsingId($admin->id);

    $this->withServerVariables([
        'REMOTE_ADDR' => '10.0.0.30',
        'HTTP_USER_AGENT' => 'TestBrowser/1.0',
    ])->get(route('admin.dashboard'))
        ->assertStatus(403);
});

it('seeds a trusted device cookie when enabling trusted device access', function () {
    AppSetting::set('ip_restriction_enabled', 'false');

    $admin = Admin::create([
        'name' => 'Operator Admin',
        'email' => 'operator@example.com',
        'password' => Hash::make('secret123'),
        'phone_number' => '0000000003',
        'is_super' => true,
    ]);

    Auth::guard('admin')->loginUsingId($admin->id);

    $response = $this->withServerVariables([
        'REMOTE_ADDR' => '10.0.0.40',
        'HTTP_USER_AGENT' => 'TestBrowser/1.0',
    ])->postJson(route('settings.security.ip-restriction.toggle'));

    $response->assertOk();
    $response->assertCookie('device_trust_token');
    $this->assertDatabaseHas('allowed_ips', [
        'added_by' => $admin->id,
        'is_active' => true,
        'label' => 'Admin Session Device',
    ]);
});
