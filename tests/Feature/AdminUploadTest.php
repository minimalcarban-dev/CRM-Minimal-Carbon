<?php

use App\Models\Admin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

uses(Tests\CreatesApplication::class);

it('allows admin to upload documents when creating an admin', function () {
    Storage::fake('public');

    // login as super admin
    $super = Admin::factory()->super()->create();
    \Illuminate\Support\Facades\Auth::guard('admin')->loginUsingId($super->id);

    $file = UploadedFile::fake()->image('aadhar.jpg');

    $response = $this->post(route('admins.store'), [
        'name' => 'Upload Admin',
        'email' => 'upload@example.com',
        'password' => 'pass12345',
        'confirm_password' => 'pass12345',
        'phone_number' => '9999999999',
        'aadhar_front_image' => $file,
    ]);

    $response->assertRedirect(route('admins.index'));

    // assert at least one file exists in the admins folder
    $files = Storage::disk('public')->allFiles('admins');
    expect(count($files))->toBeGreaterThan(0);
});
