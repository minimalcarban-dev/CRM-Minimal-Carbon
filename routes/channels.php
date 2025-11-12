<?php

use App\Models\Channel;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Admin;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat.channel.{id}', function ($user, $id) {
    // Make sure we're using the admin guard
    if (!$user instanceof \App\Models\Admin) {
        return false;
    }

    $channel = Channel::findOrFail($id);
    // Authorized if the admin is a member or the creator of the channel
    return $channel->hasMember($user) || (int) $channel->created_by === (int) $user->id;
});

// Per-admin notifications channel (membership changes, global notices)
Broadcast::channel('admin.notifications.{id}', function ($user, $id) {
    return $user instanceof Admin && (int) $user->id === (int) $id;
});