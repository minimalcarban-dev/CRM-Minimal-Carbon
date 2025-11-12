<?php

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

uses(Tests\CreatesApplication::class);

it('allows admin to login with valid credentials', function () {
    // create admin
    $admin = Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin-test@example.com',
        'password' => Hash::make('secret123'),
        'phone_number' => '0000000000',
        'is_super' => true,
    ]);

    $response = $this->post(route('admin.login.post'), [
        'email' => 'admin-test@example.com',
        'password' => 'secret123',
    ]);

    $response->assertRedirect(route('admins.index'));
    $this->assertTrue(Auth::guard('admin')->check());
});

it('rejects invalid credentials', function () {
    $response = $this->post(route('admin.login.post'), [
        'email' => 'nonexistent@example.com',
        'password' => 'nope',
    ]);

    $response->assertSessionHasErrors('email');
});
