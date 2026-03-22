<?php

use App\Models\Company;
use App\Models\Admin;
use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Order;
use App\Models\PinnedMessage;
use App\Models\Permission;
use App\Models\SavedMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Tests\TestCase::class, RefreshDatabase::class);


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

function makeTestCompany(): Company
{
    return Company::create([
        'name' => 'Test Company',
        'email' => 'company@example.test',
        'phone' => '+911234567890',
        'address' => '123 Test Street',
        'country' => 'India',
        'currency' => 'INR',
        'status' => 'active',
    ]);
}

function makeTestOrder(Company $company, array $overrides = []): Order
{
    return Order::create(array_merge([
        'order_type' => 'ready_to_ship',
        'client_name' => 'Test Client',
        'client_address' => '123 Test Street',
        'client_email' => 'client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1250.00,
    ], $overrides));
}

it('denies chat channels without auth', function () {
    $this->get('/admin/chat/channels')->assertRedirect('/admin/login');
});

it('denies chat channels without permission', function () {
    $admin = Admin::factory()->create(['is_super' => false]);
    $this->actingAs($admin, 'admin');
    $this->get('/admin/chat/channels')->assertStatus(302);
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

it('stores a single order reference in chat message metadata', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $company = makeTestCompany();
    $order = makeTestOrder($company);

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $this->post("/admin/chat/channels/{$channel->id}/messages", [
        'body' => "Please check #{$order->id}",
    ])->assertOk();

    $message = Message::query()->latest('id')->first();
    expect($message)->not->toBeNull();
    expect($message->metadata)->toHaveKey('order_refs');
    expect($message->metadata['order_refs'])->toHaveCount(1);
    expect($message->metadata['order_refs'][0])->toMatchArray([
        'id' => $order->id,
        'display_number' => (string) $order->id,
        'client_name' => $order->client_name,
        'status_key' => 'r_order_in_process',
        'status_label' => 'In Process',
        'status_color' => 'info',
        'exists' => true,
    ]);
});

it('stores multiple order references and a missing reference in thread replies', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $company = makeTestCompany();
    $orderA = makeTestOrder($company, ['client_name' => 'Client A']);
    $orderB = makeTestOrder($company, ['client_name' => 'Client B']);

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $parent = Message::create([
        'channel_id' => $channel->id,
        'sender_id' => $admin->id,
        'type' => 'text',
        'body' => 'Thread starter',
        'metadata' => [],
    ]);

    $response = $this->post("/admin/chat/messages/{$parent->id}/thread/replies", [
        'body' => "Updates for #{$orderA->id}, #{$orderB->id} and #999999",
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    $reply = Message::query()->latest('id')->first();
    expect($reply)->not->toBeNull();
    expect($reply->metadata)->toHaveKey('order_refs');
    expect($reply->metadata['order_refs'])->toHaveCount(3);
    expect($reply->metadata['order_refs'][0]['id'])->toBe($orderA->id);
    expect($reply->metadata['order_refs'][1]['id'])->toBe($orderB->id);
    expect($reply->metadata['order_refs'][2])->toMatchArray([
        'id' => 999999,
        'status_key' => 'missing',
        'status_label' => 'Order not found',
        'status_color' => 'secondary',
        'exists' => false,
    ]);
});

it('leaves order metadata empty when there are no references', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach($admin->id);

    $this->post("/admin/chat/channels/{$channel->id}/messages", [
        'body' => 'Just a normal message with no order number',
    ])->assertOk();

    $message = Message::query()->latest('id')->first();
    expect($message)->not->toBeNull();
    expect($message->metadata)->not->toHaveKey('order_refs');
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

    $message = Message::query()->latest('id')->first();
    expect($message)->not->toBeNull();
    expect($message->attachments()->count())->toBe(1);

    // mark as read
    $this->post("/admin/chat/channels/{$channel->id}/read")
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('[chat-new] toggles reactions and returns grouped counts', function () {
    $adminA = makeAdminWithChatAccess();
    $adminB = makeAdminWithChatAccess();

    $channel = Channel::factory()->create(['created_by' => $adminA->id]);
    $channel->users()->attach([$adminA->id, $adminB->id]);

    $message = Message::create([
        'channel_id' => $channel->id,
        'sender_id' => $adminB->id,
        'type' => 'text',
        'body' => 'React me',
        'metadata' => [],
    ]);

    $this->actingAs($adminA, 'admin');
    $this->post("/admin/chat/messages/{$message->id}/react", ['emoji' => ':thumbsup:'])
        ->assertOk()
        ->assertJson([
            'action' => 'added',
            'emoji' => ':thumbsup:',
        ]);

    $this->actingAs($adminB, 'admin');
    $this->post("/admin/chat/messages/{$message->id}/react", ['emoji' => ':thumbsup:'])
        ->assertOk();

    $this->actingAs($adminA, 'admin');
    $this->get("/admin/chat/channels/{$channel->id}/messages")
        ->assertOk()
        ->assertJsonPath('data.0.reactions.0.emoji', ':thumbsup:')
        ->assertJsonPath('data.0.reactions.0.count', 2);

    $this->post("/admin/chat/messages/{$message->id}/react", ['emoji' => ':thumbsup:'])
        ->assertOk()
        ->assertJson([
            'action' => 'removed',
            'emoji' => ':thumbsup:',
        ]);

    expect(
        MessageReaction::query()
            ->where('message_id', $message->id)
            ->where('emoji', ':thumbsup:')
            ->count()
    )->toBe(1);
});

it('[chat-new] pins and unpins messages with pinned list', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach([$admin->id]);

    $message = Message::create([
        'channel_id' => $channel->id,
        'sender_id' => $admin->id,
        'type' => 'text',
        'body' => 'Pin me',
        'metadata' => [],
    ]);

    $this->post("/admin/chat/channels/{$channel->id}/pin/{$message->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'pinned',
        ]);

    $this->get("/admin/chat/channels/{$channel->id}/pins")
        ->assertOk()
        ->assertJsonPath('pins.0.id', $message->id);

    expect(
        PinnedMessage::query()
            ->where('channel_id', $channel->id)
            ->where('message_id', $message->id)
            ->exists()
    )->toBeTrue();

    $this->delete("/admin/chat/channels/{$channel->id}/pin/{$message->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'action' => 'unpinned',
        ]);

    $this->get("/admin/chat/channels/{$channel->id}/pins")
        ->assertOk()
        ->assertJsonCount(0, 'pins');
});

it('[chat-new] saves and unsaves messages with saved list', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach([$admin->id]);

    $message = Message::create([
        'channel_id' => $channel->id,
        'sender_id' => $admin->id,
        'type' => 'text',
        'body' => 'Save me',
        'metadata' => [],
    ]);

    $this->post("/admin/chat/messages/{$message->id}/save")
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->get('/admin/chat/saved-messages')
        ->assertOk()
        ->assertJsonPath('saved.0.id', $message->id);

    expect(
        SavedMessage::query()
            ->where('admin_id', $admin->id)
            ->where('message_id', $message->id)
            ->exists()
    )->toBeTrue();

    $this->delete("/admin/chat/messages/{$message->id}/save")
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->get('/admin/chat/saved-messages')
        ->assertOk()
        ->assertJsonCount(0, 'saved');
});

it('[chat-new] orderSuggest supports digit query mode', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $company = makeTestCompany();
    $order = makeTestOrder($company, ['client_name' => 'Digit Mode Client']);

    $this->get('/admin/chat/orders/suggest?q=' . $order->id)
        ->assertOk()
        ->assertJsonFragment([
            'id' => $order->id,
            'client_name' => 'Digit Mode Client',
        ]);
});

it('[chat-new] orderSuggest supports letter query mode', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $company = makeTestCompany();
    $order = makeTestOrder($company, ['client_name' => 'LetterMatch Client']);

    $this->get('/admin/chat/orders/suggest?q=LetterMatch')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $order->id,
            'client_name' => 'LetterMatch Client',
        ]);
});

it('[chat-new] orderSuggest supports mixed query mode', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $company = makeTestCompany();
    $order = makeTestOrder($company, ['client_name' => 'Alpha9 Customer']);

    // Mixed input: digits + letters. ID prefix "9" does not match this order id,
    // so this validates that client_name matching still runs in mixed mode.
    $this->get('/admin/chat/orders/suggest?q=Alpha9')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $order->id,
            'client_name' => 'Alpha9 Customer',
        ]);
});

it('[chat-new] orderSuggest excludes order_url from payload', function () {
    $admin = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $company = makeTestCompany();
    $order = makeTestOrder($company, ['client_name' => 'No Url Field']);

    $this->get('/admin/chat/orders/suggest?q=No')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $order->id,
            'client_name' => 'No Url Field',
        ])
        ->assertJsonMissingPath('orders.0.order_url');
});

it('[chat-new] message payload includes reactions and pin/save flags', function () {
    $admin = makeAdminWithChatAccess();
    $other = makeAdminWithChatAccess();
    $this->actingAs($admin, 'admin');

    $channel = Channel::factory()->create(['created_by' => $admin->id]);
    $channel->users()->attach([$admin->id, $other->id]);

    $message = Message::create([
        'channel_id' => $channel->id,
        'sender_id' => $other->id,
        'type' => 'text',
        'body' => 'Payload enriched',
        'metadata' => [],
    ]);

    MessageReaction::create([
        'message_id' => $message->id,
        'admin_id' => $admin->id,
        'emoji' => ':thumbsup:',
    ]);

    PinnedMessage::create([
        'message_id' => $message->id,
        'channel_id' => $channel->id,
        'pinned_by' => $admin->id,
    ]);

    SavedMessage::create([
        'message_id' => $message->id,
        'admin_id' => $admin->id,
    ]);

    $this->get("/admin/chat/channels/{$channel->id}/messages")
        ->assertOk()
        ->assertJsonPath('data.0.is_pinned', true)
        ->assertJsonPath('data.0.is_saved', true)
        ->assertJsonPath('data.0.reactions.0.emoji', ':thumbsup:')
        ->assertJsonPath('data.0.reactions.0.count', 1);
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
