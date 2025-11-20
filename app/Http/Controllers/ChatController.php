<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Jobs\ProcessChatAttachment;
use App\Services\VirusScanner;

class ChatController extends Controller
{
    /**
     * Show the chat interface
     */
    public function index()
    {
        // Render chat interface; channels and selection are handled by the SPA
        $user = Auth::guard('admin')->user();
        return view('chat.index', [
            'currentAdmin' => $user
        ]);
    }

    /**
     * Create a new channel
     */
    public function createChannel(Request $request)
    {
        $current = Auth::guard('admin')->user();
        if (!$current) {
            return response()->json(['errors' => ['auth' => ['Unauthorized']]], 401);
        }
        // Only super admin can create channels (as per requirement)
        if (!$current->is_super) {
            return response()->json(['errors' => ['auth' => ['Forbidden']]], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'users' => 'required|array',
            'users.*' => 'exists:admins,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $creatorId = Auth::guard('admin')->id();
        $channel = Channel::create([
            'name' => $request->name,
            'description' => $request->input('description'),
            'type' => 'group',
            'created_by' => $creatorId,
        ]);

        // Add the creator and other users to the channel
        $users = array_unique(array_merge($request->users, [$creatorId]));
        $channel->users()->attach($users);

        return response()->json($channel->load('users'));
    }

    /**
     * Get channels for the authenticated user
     */
    public function getChannels()
    {
        /** @var \App\Models\Admin $user */
        $user = Auth::guard('admin')->user();
        $channels = Channel::whereHas('users', function ($query) use ($user) {
            $query->where('admin_id', $user->id);
        })->get();

        // Add unread count to each channel
        $channels->each(function ($channel) use ($user) {
            $channel->unread_count = $channel->unreadCount($user);
            // also expose with a frontend-friendly name used in some UIs
            $channel->unread_messages_count = $channel->unread_count;
            $channel->can_manage_members = ($user->is_super || (int) $channel->created_by === (int) $user->id);
        });

        return response()->json($channels);
    }

    /**
     * Create or fetch a direct (personal) channel between current admin and target admin.
     */
    public function direct(Request $request)
    {
        $current = Auth::guard('admin')->user();
        if (!$current) {
            return response()->json(['errors' => ['auth' => ['Unauthorized']]], 401);
        }

        $validator = Validator::make($request->all(), [
            'target_admin_id' => 'required|exists:admins,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $targetId = (int) $request->input('target_admin_id');
        if ($targetId === (int) $current->id) {
            return response()->json(['errors' => ['target_admin_id' => ['Cannot create a DM with yourself']]], 422);
        }

        // Try to find existing personal channel involving exactly these two users
        $existing = Channel::where('type', 'personal')
            ->whereHas('users', function ($q) use ($current) {
                $q->where('admin_id', $current->id);
            })
            ->whereHas('users', function ($q) use ($targetId) {
                $q->where('admin_id', $targetId);
            })
            ->whereDoesntHave('users', function ($q) use ($current, $targetId) {
                $q->whereNotIn('admin_id', [$current->id, $targetId]);
            })
            ->first();

        if ($existing) {
            return response()->json($existing->load('users'));
        }

        // Create new personal channel (auto-add super admins for oversight when neither party is super)
        $target = \App\Models\Admin::findOrFail($targetId);
        $channel = Channel::create([
            'name' => $target->name,
            'type' => 'personal',
            'created_by' => $current->id,
        ]);

        $members = [$current->id, $targetId];
        if (!$current->is_super && !$target->is_super) {
            $superIds = \App\Models\Admin::where('is_super', true)->pluck('id')->all();
            $members = array_unique(array_merge($members, $superIds));
        }
        $channel->users()->attach($members);

        \App\Services\AuditLogger::log(
            event: 'channel.direct.created',
            auditable: $channel,
            userId: $current->id,
            newValues: ['member_ids' => $members]
        );

        return response()->json($channel->load('users'));
    }

    /**
     * List all admins for channel creation or membership management
     * Only super admin can fetch the list (to drive UI capability)
     */
    public function listAdmins()
    {
        $current = Auth::guard('admin')->user();
        if (!$current) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!$current->is_super) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $admins = \App\Models\Admin::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json(['admins' => $admins]);
    }

    /**
     * Send a message to a channel
     */
    public function sendMessage(Request $request, Channel $channel)
    {
        $user = Auth::guard('admin')->user();

        // Check if user belongs to channel
        if (!$channel->users()->where('admin_id', $user->id)->exists()) {
            // Hide resource existence from non-members
            return response()->json(['error' => 'Not Found'], 404);
        }

        $maxMb = (int) config('chat.max_upload_mb', 10);
        $maxKb = $maxMb * 1024; // Laravel's max is in KB
        $allowedMimes = (array) config('chat.allowed_mime_types', []);

        $validator = Validator::make($request->all(), [
            'body' => 'required_without:attachments|string|max:10000',
            'attachments.*' => ['file', 'max:' . $maxKb, 'mimetypes:' . implode(',', $allowedMimes)],
            'type' => 'sometimes|string|in:text,image,file',
            'metadata' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = $channel->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->body,
            'type' => $request->type ?? 'text',
            'metadata' => $request->metadata
        ]);

        // Handle file attachments with strict validation and scanning
        if ($request->hasFile('attachments')) {
            $scanner = app(VirusScanner::class);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) {
                    continue;
                }

                // Magic bytes check
                $realMime = $finfo->file($file->getRealPath());
                if (!in_array($realMime, $allowedMimes, true)) {
                    // Skip and optionally accumulate an error; for now, skip silently and continue
                    continue;
                }

                // Store file first (scanning will operate on stored path)
                $path = $file->store('chat-attachments', 'public');
                $absPath = Storage::disk('public')->path($path);

                // Virus scan
                $scan = $scanner->scan($absPath);
                if (empty($scan['clean'])) {
                    // Infected or scan error: remove file and skip
                    @Storage::disk('public')->delete($path);
                    continue;
                }

                // Persist attachment record
                $attachment = $message->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $realMime,
                    'size' => $file->getSize()
                ]);

                // Dispatch async processing (e.g., thumbnails)
                ProcessChatAttachment::dispatch($attachment);
            }
        }

        // Load relationships and broadcast
        $message->load(['sender', 'attachments']);
        broadcast(new MessageSent($message))->toOthers();

        // Broadcast mention notifications to each mentioned admin (if provided)
        $mentions = $request->input('metadata.mentions', []);
        if (is_array($mentions) && count($mentions)) {
            foreach ($mentions as $mentionedId) {
                try {
                    broadcast(new \App\Events\UserMentioned((int)$mentionedId, $message));
                } catch (\Throwable $e) {
                    // swallow broadcast exceptions to avoid breaking send flow
                }
            }
        }

        // Extract and persist links for efficient sidebar queries
        if (!empty($request->body)) {
            $pattern = "/\\bhttps?:\\/\\/[^\\s<>()\"']+/i";
            if (preg_match_all($pattern, $request->body, $matches)) {
                $urls = array_unique($matches[0] ?? []);
                foreach ($urls as $url) {
                    \App\Models\MessageLink::create([
                        'message_id' => $message->id,
                        'url' => $url,
                    ]);
                }
            }
        }

        \App\Services\AuditLogger::log(
            event: 'message.sent',
            auditable: $message,
            userId: $user->id,
            newValues: [
                'channel_id' => $channel->id,
                'attachments' => $message->attachments->pluck('id')->all(),
            ]
        );

        return response()->json($message);
    }

    /**
     * Get messages for a channel with pagination
     */
    public function getMessages(Channel $channel, Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        if (!$channel->users()->where('admin_id', $adminId)->exists()) {
            // Hide resource existence from non-members
            return response()->json(['error' => 'Not Found'], 404);
        }

        $messages = $channel->messages()
            ->with(['sender', 'attachments', 'reads'])
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Channel $channel)
    {
        $user = Auth::guard('admin')->user();

        if (!$channel->users()->where('admin_id', $user->id)->exists()) {
            // Hide resource existence from non-members
            return response()->json(['error' => 'Not Found'], 404);
        }
        $unreadMessages = $channel->messages()
            ->whereDoesntHave('reads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        foreach ($unreadMessages as $message) {
            $message->reads()->create([
                'user_id' => $user->id,
                'read_at' => now()
            ]);
        }

        // Persist read position for accurate unread counts across sessions
        $channel->users()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        broadcast(new MessagesRead($channel->id, $user->id))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Sidebar data: channel info, members, recent images/files/links
     */
    public function sidebar(Channel $channel)
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!$channel->users()->where('admin_id', $user->id)->exists()) {
            // Hide resource existence from non-members
            return response()->json(['error' => 'Not Found'], 404);
        }

        // Channel info
        $creator = \App\Models\Admin::select('id', 'name', 'email')->find($channel->created_by);
        $members = $channel->users()->select('admins.id', 'admins.name', 'admins.email')->get();

        // Attachments
        $images = \App\Models\MessageAttachment::whereHas('message', function ($q) use ($channel) {
            $q->where('channel_id', $channel->id);
        })
            ->where('mime_type', 'like', 'image/%')
            ->latest('id')
            ->limit(30)
            ->get(['id', 'filename', 'path', 'thumbnail_path', 'mime_type', 'size', 'message_id', 'created_at']);

        $files = \App\Models\MessageAttachment::whereHas('message', function ($q) use ($channel) {
            $q->where('channel_id', $channel->id);
        })
            ->where('mime_type', 'not like', 'image/%')
            ->latest('id')
            ->limit(30)
            ->get(['id', 'filename', 'path', 'mime_type', 'size', 'message_id', 'created_at']);

        // Links: read from indexed table for efficiency
        $links = \App\Models\MessageLink::whereHas('message', function ($q) use ($channel) {
            $q->where('channel_id', $channel->id);
        })
            ->with(['message:id,sender_id,created_at', 'message.sender:id,name'])
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(function ($lnk) {
                return [
                    'url' => $lnk->url,
                    'message_id' => $lnk->message_id,
                    'created_at' => optional($lnk->message)->created_at,
                    'sender' => [
                        'id' => optional($lnk->message)->sender_id,
                        'name' => optional(optional($lnk->message)->sender)->name,
                    ],
                ];
            });

        return response()->json([
            'channel' => [
                'id' => $channel->id,
                'name' => $channel->name,
                'type' => $channel->type,
                'description' => $channel->description,
                'created_at' => $channel->created_at,
                'creator' => $creator,
                'members' => $members,
                // Server-driven UI hints
                'show_sidebar' => $channel->type !== 'public',
            ],
            'images' => $images,
            'files' => $files,
            'links' => $links,
        ]);
    }

    /**
     * Search messages
     */
    public function searchMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'channel_id' => 'sometimes|exists:channels,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::guard('admin')->user();

        // Get channels the user has access to
        // Get IDs of channels where the user is a member
        $query = Message::query()
            ->whereHas('channel.users', function ($q) use ($user) {
                $q->where('admin_id', $user->id);
            })
            ->where('body', 'like', '%' . $request->input('query') . '%');

        if ($request->channel_id) {
            $query->where('channel_id', $request->channel_id);
        }

        $messages = $query->get();
        $messages->load(['sender', 'attachments', 'channel']);

        return response()->json($messages);
    }

    /**
     * List channel members and all admins (for manage members UI)
     */
    public function getChannelMembers(Channel $channel)
    {
        $current = Auth::guard('admin')->user();
        if (!$current) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only owner or super admin can manage/view full membership list
        if (!($current->is_super || (int) $channel->created_by === (int) $current->id)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $members = $channel->users()->pluck('admins.id');
        $allAdmins = \App\Models\Admin::select('id', 'name', 'email')->orderBy('name')->get();

        return response()->json([
            'channel' => ['id' => $channel->id, 'name' => $channel->name],
            'member_ids' => $members,
            'admins' => $allAdmins,
        ]);
    }

    /**
     * Update channel members (owner or super admin only)
     */
    public function updateChannelMembers(Request $request, Channel $channel)
    {
        $current = Auth::guard('admin')->user();
        if (!$current) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!($current->is_super || (int) $channel->created_by === (int) $current->id)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'member_ids' => 'array',
            'member_ids.*' => 'exists:admins,id'
        ]);

        $memberIds = $data['member_ids'] ?? [];
        // Ensure owner always remains a member
        if (!in_array($channel->created_by, $memberIds)) {
            $memberIds[] = $channel->created_by;
        }
        // Track membership changes
        $existing = $channel->users()->pluck('admins.id')->all();
        $desired = array_values(array_unique($memberIds));
        $channel->users()->sync($desired);

        $removed = array_values(array_diff($existing, $desired));
        $added = array_values(array_diff($desired, $existing));

        // Notify affected users via per-admin notification channel
        foreach ($removed as $adminId) {
            broadcast(new \App\Events\ChannelMembershipChanged($adminId, $channel->id, 'removed'));
        }
        foreach ($added as $adminId) {
            broadcast(new \App\Events\ChannelMembershipChanged($adminId, $channel->id, 'added'));
        }

        \App\Services\AuditLogger::log(
            event: 'channel.members.updated',
            auditable: $channel,
            userId: $current->id,
            oldValues: ['previous_member_ids' => $existing],
            newValues: ['current_member_ids' => $desired]
        );

        return response()->json(['success' => true]);
    }
}
