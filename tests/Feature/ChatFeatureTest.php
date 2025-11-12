<?php

use App\Models\Admin;
use App\Models\Channel;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeAdminWithChatAccess(): Admin {
    /** @var Admin $admin */
    $admin = Admin::factory()->create(['is_super' => false]);
    $perm = Permission::updateOrCreate(
        ['slug' => 'chat.access'],
        ['name' => 'Access Chat', 'description' => 'Access and use the chat system']
    );
    $admin->permissions()->syncWithoutDetaching([$perm->id]);
    $admin->clearPermissionCache();
    return $admin;
}

it('denies chat channels without auth', function () {
    $this->get('/admin/chat/channels')->assertRedirect('/admin/login');
});

it('denies chat channels without permission', function () {
    $admin = Admin::factory()->create(['is_super' => false]);
    $this->actingAs($admin, 'admin');
    $this->get('/admin/chat/channels')->assertStatus(403);
});

it('allows chat channels with permission', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    // Ensure there is at least one channel membership
    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $this->get('/admin/chat/channels')->assertOk();
});

it('can fetch messages for a channel the admin belongs to', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $this->get("/admin/chat/channels/{$channel->id}/messages")
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('can send a text message', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $this->post("/admin/chat/channels/{$channel->id}/messages", [
        'body' => 'Hello world from test',
    ])->assertOk()->assertJsonFragment([
        'body' => 'Hello world from test'
    ]);
});

it('can send an attachment and mark messages as read', function () {
    Storage::fake('public');
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

    $this->post("/admin/chat/channels/{$channel->id}/messages", [
        'attachments' => [$file],
    ])->assertOk();

    // ensure at least one file stored under chat-attachments
    expect(Storage::disk('public')->allFiles('chat-attachments'))
        ->not->toBeEmpty();

    // mark as read
    $this->post("/admin/chat/channels/{$channel->id}/read")
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('broadcast auth rejects unauthenticated', function () {
    $this->withHeaders(['Accept' => 'application/json'])
        ->post('/admin/broadcasting/auth', [
            'channel_name' => 'private-chat.channel.1',
            'socket_id' => '123.456',
        ])->assertStatus(401);
});

it('broadcast auth allows member of channel', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $this->post('/admin/broadcasting/auth', [
        'channel_name' => 'private-chat.channel.' . $channel->id,
        'socket_id' => '123.456',
    ])->assertOk();
});
