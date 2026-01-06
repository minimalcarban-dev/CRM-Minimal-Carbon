<?php

namespace App\Http\Controllers;

use App\Events\ChannelMembershipChanged;
use App\Models\Admin;
use App\Models\Channel;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageLink;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Jobs\ProcessChatAttachment;
use App\Services\VirusScanner;
use App\Events\BroadcastTest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{

    /**
     * Diagnostic endpoint to test broadcasting.
     * Handles GET to show the test page and POST to trigger an event.
     */
    public function testBroadcast(Request $request)
    {
        // If the request is a GET request, show the test page
        if ($request->isMethod('get')) {
            return view('admin.broadcast-test');
        }

        // If the request is a POST request, dispatch the event
        $message = $request->input('message', 'This is a test broadcast from the test endpoint.');
        Log::info('[BroadcastTest] Endpoint hit via POST. Dispatching event...');

        try {
            broadcast(new BroadcastTest($message));
            Log::info('[BroadcastTest] Event dispatched successfully.');
            return response()->json([
                'status' => 'success',
                'message' => 'BroadcastTest event has been dispatched.',
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('[BroadcastTest] Failed to dispatch event.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to dispatch event. Check storage/logs/laravel.log for details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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

        try {
            DB::beginTransaction();

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

            DB::commit();

            // Broadcast membership change to all members for real-time channel list update
            foreach ($users as $userId) {
                broadcast(new ChannelMembershipChanged($userId, $channel->id, 'added'));
            }

            Log::info('Channel created', [
                'channel_id' => $channel->id,
                'name' => $channel->name,
                'members_count' => count($users),
                'created_by' => $creatorId
            ]);

            return response()->json($channel->load('users'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Channel creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => $current->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to create channel'], 500);
        }
    }

    /**
     * Get channels for the authenticated user
     */
    // public function getChannels()
    // {
    //     /** @var Admin $user */
    //     $user = Auth::guard('admin')->user();
    //     $channels = Channel::whereHas('users', function ($query) use ($user) {
    //         $query->where('admin_id', $user->id);
    //     })->with('users:admins.id,admins.name,admins.email')->get();

    //     // Add unread count to each channel
    //     $channels->each(function ($channel) use ($user) {
    //         $channel->unread_count = $channel->unreadCount($user);
    //         // also expose with a frontend-friendly name used in some UIs
    //         $channel->unread_messages_count = $channel->unread_count;
    //         $channel->can_manage_members = ($user->is_super || (int) $channel->created_by === (int) $user->id);

    //         // For personal channels, show the other person's name
    //         if ($channel->type === 'personal') {
    //             $otherUser = $channel->users->firstWhere('id', '!=', $user->id);
    //             if ($otherUser) {
    //                 $channel->name = $otherUser->name;
    //             }
    //         }
    //     });

    //     return response()->json($channels);
    // }

    public function getChannels()
    {
        /** @var Admin $user */
        $user = Auth::guard('admin')->user();

        // Load channels with users data (needed for calculating names)
        $channels = Channel::with([
            'users' => function ($q) {
                $q->select('admins.id', 'name', 'is_super');
            }
        ])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('admin_id', $user->id);
            })->get();

        // Add unread count and CALCULATE DYNAMIC NAME
        $channels->each(function ($channel) use ($user) {
            $channel->unread_count = $channel->unreadCount($user);
            $channel->unread_messages_count = $channel->unread_count;
            $channel->can_manage_members = ($user->is_super || (int) $channel->created_by === (int) $user->id);

            // Get last message for preview
            $lastMessage = $channel->messages()
                ->with('sender:id,name')
                ->latest()
                ->first();

            $channel->last_message = $lastMessage;

            // --- LOGIC START: Dynamic Naming for Personal Chats ---
            if ($channel->type === 'personal') {
                // Get everyone in the chat except the current logged-in user
                $otherParticipants = $channel->users->filter(function ($u) use ($user) {
                    return $u->id !== $user->id;
                });

                if ($user->is_super) {
                    // IF SUPER ADMIN:
                    // Join the names of the other participants.
                    // Example: If watching Pari and Shreya, result is "Pari - Shreya"
                    $names = $otherParticipants->pluck('name')->toArray();
                    // If array is empty (talking to self), use own name, otherwise join with " - "
                    $channel->name = !empty($names) ? implode(' - ', $names) : $user->name;
                } else {
                    // IF NORMAL USER:
                    // Find the partner. Filter out Super Admins (silent monitors) unless talking directly to one.
                    $partner = $otherParticipants->first(function ($u) {
                        return !$u->is_super;
                    });

                    // Fallback: If chatting directly WITH a super admin, show them.
                    if (!$partner) {
                        $partner = $otherParticipants->first();
                    }

                    if ($partner) {
                        $channel->name = $partner->name;
                    }
                }
            }
            // --- LOGIC END ---
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

        try {
            DB::beginTransaction();

            // Create new personal channel (auto-add super admins for oversight when neither party is super)
            $target = Admin::findOrFail($targetId);
            $channel = Channel::create([
                'name' => $target->name,
                'type' => 'personal',
                'created_by' => $current->id,
            ]);

            $members = [$current->id, $targetId];
            if (!$current->is_super && !$target->is_super) {
                $superIds = Admin::where('is_super', true)->pluck('id')->all();
                $members = array_unique(array_merge($members, $superIds));
            }
            $channel->users()->attach($members);

            AuditLogger::log(
                event: 'channel.direct.created',
                auditable: $channel,
                userId: $current->id,
                newValues: ['member_ids' => $members]
            );

            DB::commit();

            // Broadcast membership change to all members for real-time channel list update
            foreach ($members as $memberId) {
                broadcast(new ChannelMembershipChanged($memberId, $channel->id, 'added'));
            }

            Log::info('Direct channel created', [
                'channel_id' => $channel->id,
                'members' => $members,
                'created_by' => $current->id
            ]);

            return response()->json($channel->load('users'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Direct channel creation failed', [
                'error' => $e->getMessage(),
                'target_admin_id' => $targetId,
                'current_admin_id' => $current->id
            ]);
            return response()->json(['error' => 'Failed to create direct channel'], 500);
        }
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

        $admins = Admin::select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json(['admins' => $admins]);
    }

    /**
     * Send a message to a channel
     */
    // public function sendMessage(Request $request, Channel $channel)
    // {
    //     $user = Auth::guard('admin')->user();

    //     // Check if user belongs to channel
    //     if (!$channel->users()->where('admin_id', $user->id)->exists()) {
    //         // Hide resource existence from non-members
    //         return response()->json(['error' => 'Not Found'], 404);
    //     }

    //     $maxMb = (int) config('chat.max_upload_mb', 10);
    //     $maxKb = $maxMb * 1024; // Laravel's max is in KB
    //     $allowedMimes = (array) config('chat.allowed_mime_types', []);

    //     $validator = Validator::make($request->all(), [
    //         'body' => 'required_without:attachments|string|max:10000',
    //         'attachments.*' => ['file', 'max:' . $maxKb, 'mimetypes:' . implode(',', $allowedMimes)],
    //         'type' => 'sometimes|string|in:text,image,file',
    //         'metadata' => 'sometimes|array'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $message = $channel->messages()->create([
    //             'sender_id' => $user->id,
    //             'body' => $request->body,
    //             'type' => $request->type ?? 'text',
    //             'metadata' => $request->metadata
    //         ]);

    //         $attachmentIds = [];
    //         $failedAttachments = 0;

    //         // Handle file attachments with strict validation and scanning
    //         if ($request->hasFile('attachments')) {
    //             $scanner = app(VirusScanner::class);
    //             $finfo = new \finfo(FILEINFO_MIME_TYPE);

    //             foreach ($request->file('attachments') as $file) {
    //                 try {
    //                     if (!$file->isValid()) {
    //                         $failedAttachments++;
    //                         Log::warning('Invalid file upload in chat', ['filename' => $file->getClientOriginalName()]);
    //                         continue;
    //                     }

    //                     // Magic bytes check
    //                     $realMime = $finfo->file($file->getRealPath());
    //                     if (!in_array($realMime, $allowedMimes, true)) {
    //                         $failedAttachments++;
    //                         Log::warning('File MIME type not allowed in chat', [
    //                             'filename' => $file->getClientOriginalName(),
    //                             'detected_mime' => $realMime
    //                         ]);
    //                         continue;
    //                     }

    //                     // Store file first (scanning will operate on stored path)
    //                     $path = $file->store('chat-attachments', 'public');
    //                     $absPath = Storage::disk('public')->path($path);

    //                     // Virus scan
    //                     $scan = $scanner->scan($absPath);
    //                     if (empty($scan['clean'])) {
    //                         // Infected or scan error: remove file and skip
    //                         Storage::disk('public')->delete($path);
    //                         $failedAttachments++;
    //                         Log::warning('File failed virus scan in chat', [
    //                             'filename' => $file->getClientOriginalName(),
    //                             'scan_result' => $scan
    //                         ]);
    //                         continue;
    //                     }

    //                     // Persist attachment record
    //                     $attachment = $message->attachments()->create([
    //                         'filename' => $file->getClientOriginalName(),
    //                         'path' => $path,
    //                         'mime_type' => $realMime,
    //                         'size' => $file->getSize()
    //                     ]);

    //                     $attachmentIds[] = $attachment->id;

    //                     // Dispatch async processing (e.g., thumbnails)
    //                     ProcessChatAttachment::dispatch($attachment);

    //                 } catch (\Exception $e) {
    //                     $failedAttachments++;
    //                     Log::error('Attachment processing failed', [
    //                         'filename' => $file->getClientOriginalName(),
    //                         'error' => $e->getMessage()
    //                     ]);
    //                 }
    //             }
    //         }

    //         // Extract and persist links for efficient sidebar queries (improved regex)
    //         if (!empty($request->body)) {
    //             $pattern = "/(?:https?:\\/\\/)?(?:www\\.)?[a-z0-9-]+(?:\\.[a-z0-9-]+)*\\.[a-z]{2,}(?:\\/[^\\s<>()\"']*)?/i";

    //             if (preg_match_all($pattern, $request->body, $matches)) {
    //                 $urls = array_unique($matches[0] ?? []);
    //                 foreach ($urls as $url) {
    //                     $cleanUrl = $url;
    //                     if (!preg_match("~^(?:f|ht)tps?://~i", $cleanUrl)) {
    //                         $cleanUrl = "https://" . $cleanUrl;
    //                     }

    //                     MessageLink::create([
    //                         'message_id' => $message->id,
    //                         'url' => $cleanUrl,
    //                     ]);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         // Load relationships and broadcast
    //         $message->load(['sender', 'attachments']);

    //         try {
    //             broadcast(new MessageSent($message))->toOthers();

    //             // Broadcast mention notifications to each mentioned admin
    //             $mentions = $request->input('metadata.mentions', []);
    //             if (is_array($mentions) && count($mentions)) {
    //                 Log::info('[MENTION] Broadcasting UserMentioned events', [
    //                     'message_id' => $message->id,
    //                     'mentions' => $mentions
    //                 ]);
    //                 foreach ($mentions as $mentionedId) {
    //                     Log::info('[MENTION] Broadcasting to admin', ['admin_id' => $mentionedId]);
    //                     broadcast(new \App\Events\UserMentioned((int) $mentionedId, $message));
    //                 }
    //             } else {
    //                 Log::info('[MENTION] No mentions found in message', ['message_id' => $message->id]);
    //             }
    //         } catch (\Throwable $e) {
    //             // Swallow broadcast exceptions to avoid breaking send flow
    //             Log::warning('Broadcast failed but message saved', [
    //                 'message_id' => $message->id,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }

    //         AuditLogger::log(
    //             event: 'message.sent',
    //             auditable: $message,
    //             userId: $user->id,
    //             newValues: [
    //                 'channel_id' => $channel->id,
    //                 'attachments' => $attachmentIds,
    //                 'failed_attachments' => $failedAttachments
    //             ]
    //         );

    //         Log::info('Message sent', [
    //             'message_id' => $message->id,
    //             'channel_id' => $channel->id,
    //             'attachments_count' => count($attachmentIds),
    //             'failed_attachments' => $failedAttachments,
    //             'sender_id' => $user->id
    //         ]);

    //         return response()->json($message);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Message sending failed', [
    //             'channel_id' => $channel->id,
    //             'sender_id' => $user->id,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['error' => 'Failed to send message'], 500);
    //     }
    // }
    public function sendMessage(Request $request, $channelId)
    {
        $userId = Auth::guard('admin')->id();

        if (!$userId) {
            Log::error('Chat send failed: sender not authenticated');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Load channel properly (FIX for 500 error)
        $channel = Channel::findOrFail($channelId);

        // Security: user must belong to channel
        if (!$channel->users()->where('admin_id', $userId)->exists()) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        // Validation
        $maxMb = (int) config('chat.max_upload_mb', 10);
        $maxKb = $maxMb * 1024;

        $allowedMimes = (array) config('chat.allowed_mime_types', []);

        $validator = Validator::make($request->all(), [
            'body' => 'required_without:attachments|string|max:10000',
            'attachments.*' => [
                'file',
                'max:' . $maxKb,
                'mimetypes:' . implode(',', $allowedMimes),
            ],
            'metadata' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Create message
            $message = $channel->messages()->create([
                'sender_id' => $userId,
                'body' => $request->input('body', ''),
                'type' => 'text',
                'metadata' => $request->input('metadata', []),
            ]);

            $scanner = app(VirusScanner::class);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);

            // Handle attachments (SYNC + SAFE)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }

                    $realMime = $finfo->file($file->getRealPath());

                    if (!in_array($realMime, $allowedMimes, true)) {
                        continue;
                    }

                    // Store file
                    // $path = $file->store('chat-attachments', 'public');
                    $path = $file->store('uploads/chat-attachments', 'public');
                    $abs = Storage::disk('public')->path($path);

                    // Virus scan
                    $scan = $scanner->scan($abs);
                    if (empty($scan['clean'])) {
                        Storage::disk('public')->delete($path);
                        continue;
                    }

                    // Save attachment record
                    $attachment = $message->attachments()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $realMime,
                        'size' => $file->getSize(),
                    ]);

                    // Process attachment safely (NO QUEUE)
                    try {
                        (new \App\Jobs\ProcessChatAttachment($attachment))->handle();
                    } catch (\Throwable $e) {
                        Log::error('Attachment processing failed (non-blocking)', [
                            'attachment_id' => $attachment->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }


            // Process mentions (if any)
            $mentions = $request->input('metadata.mentions');
            if (!empty($mentions) && is_array($mentions)) {
                foreach ($mentions as $adminId) {
                    try {
                        // Dispatch event for each mentioned user
                        // This triggers the SendChatMentionNotification listener
                        event(new \App\Events\UserMentioned((int) $adminId, $message));
                    } catch (\Throwable $e) {
                        Log::error('Failed to dispatch mention event', [
                            'mentioned_id' => $adminId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Extract and persist links for efficient sidebar queries
            // First, remove email addresses to prevent extracting domains from them
            if (!empty($request->body)) {
                // Remove emails first to prevent matching domains like gmail.com from user@gmail.com
                $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
                $bodyWithoutEmails = preg_replace($emailPattern, '', $request->body);

                // Now find URLs in the cleaned text
                $pattern = '/(?:https?:\/\/)?(?:www\.)?[a-z0-9][-a-z0-9]*(?:\.[a-z0-9][-a-z0-9]*)+(?:\/[^\s<>()"\']*)?\b/i';

                if (preg_match_all($pattern, $bodyWithoutEmails, $matches)) {
                    $urls = array_unique($matches[0] ?? []);
                    foreach ($urls as $url) {
                        $cleanUrl = trim($url);
                        if (!preg_match('~^https?://~i', $cleanUrl)) {
                            $cleanUrl = 'https://' . $cleanUrl;
                        }

                        MessageLink::firstOrCreate([
                            'message_id' => $message->id,
                            'url' => $cleanUrl,
                        ]);
                    }
                }
            }

            DB::commit();

            // Load relations for frontend
            $message->load(['sender', 'attachments', 'reads']);

            // Broadcast (safe)
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('Broadcast failed but message saved', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json($message);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Message send failed', [
                'channel_id' => $channelId,
                'sender_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to send message'], 500);
        }
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
            ->whereNull('reply_to_id')
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

        try {
            DB::beginTransaction();

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

            DB::commit();

            try {
                broadcast(new MessagesRead($channel->id, $user->id))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('Broadcast failed for messages read', [
                    'channel_id' => $channel->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Messages marked as read', [
                'channel_id' => $channel->id,
                'user_id' => $user->id,
                'count' => $unreadMessages->count()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark as read failed', [
                'channel_id' => $channel->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to mark messages as read'], 500);
        }
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
        $creator = Admin::select('id', 'name', 'email')->find($channel->created_by);
        $members = $channel->users()->select('admins.id', 'admins.name', 'admins.email')->get();

        // For personal channels, determine the display name (the other person's name)
        $displayName = $channel->name;
        // if ($channel->type === 'personal') {
        //     $otherUser = $members->firstWhere('id', '!=', $user->id);
        //     if ($otherUser) {
        //         $displayName = $otherUser->name;
        //     }
        // }

        if ($channel->type === 'personal') {
            // Re-fetch users to be sure we have is_super data if needed, 
            // or use the $members collection we already fetched in your code:
            // $members = $channel->users()->select(...)->get();

            // Using the $members collection created in your existing code:
            $others = $members->filter(fn($m) => $m->id !== $user->id);

            if ($user->is_super) {
                $names = $others->pluck('name')->toArray();
                $displayName = !empty($names) ? implode(' - ', $names) : $user->name;
            } else {
                // Note: You might need to ensure 'is_super' is selected in your $members query in the original file
                // If not available, just pick the first other person:
                $partner = $others->first();
                if ($partner)
                    $displayName = $partner->name;
            }
        }

        // Attachments
        $images = MessageAttachment::whereHas('message', function ($q) use ($channel) {
            $q->where('channel_id', $channel->id);
        })
            ->where('mime_type', 'like', 'image/%')
            ->latest('id')
            ->limit(30)
            ->get(['id', 'filename', 'path', 'thumbnail_path', 'mime_type', 'size', 'message_id', 'created_at']);

        $files = MessageAttachment::whereHas('message', function ($q) use ($channel) {
            $q->where('channel_id', $channel->id);
        })
            ->where('mime_type', 'not like', 'image/%')
            ->latest('id')
            ->limit(30)
            ->get(['id', 'filename', 'path', 'mime_type', 'size', 'message_id', 'created_at']);

        // Links: read from indexed table for efficiency
        $links = MessageLink::whereHas('message', function ($q) use ($channel) {
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
                'name' => $displayName, // Use the dynamic display name
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
        $allAdmins = Admin::select('id', 'name', 'email')->orderBy('name')->get();

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
        $validator = Validator::make($request->all(), [
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:admins,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $memberIds = $request->input('member_ids', []);

        // Only owner or super admin can manage/view full membership list
        if (!($current->is_super || (int) $channel->created_by === (int) $current->id)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        DB::beginTransaction();

        try {
            // Track membership changes
            $existing = $channel->users()->pluck('admins.id')->all();
            $desired = array_values(array_unique($memberIds));
            $channel->users()->sync($desired);

            $removed = array_values(array_diff($existing, $desired));
            $added = array_values(array_diff($desired, $existing));

            AuditLogger::log(
                event: 'channel.members.updated',
                auditable: $channel,
                userId: $current->id,
                oldValues: ['previous_member_ids' => $existing],
                newValues: ['current_member_ids' => $desired]
            );

            DB::commit();

            // Notify affected users via per-admin notification channel
            try {
                foreach ($removed as $adminId) {
                    broadcast(new ChannelMembershipChanged($adminId, $channel->id, 'removed'));
                }
                foreach ($added as $adminId) {
                    broadcast(new ChannelMembershipChanged($adminId, $channel->id, 'added'));

                    // Send notification to the added user
                    $addedAdmin = Admin::find($adminId);
                    if ($addedAdmin) {
                        $addedAdmin->notify(new \App\Notifications\ChannelAddedNotification($channel, $current));
                        Log::info('Channel added notification sent', [
                            'channel_id' => $channel->id,
                            'admin_id' => $adminId,
                            'added_by' => $current->id
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Membership broadcast failed', [
                    'channel_id' => $channel->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Channel members updated', [
                'channel_id' => $channel->id,
                'added_count' => count($added),
                'removed_count' => count($removed),
                'updated_by' => $current->id
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Channel members update failed', [
                'channel_id' => $channel->id,
                'error' => $e->getMessage(),
                'admin_id' => $current->id
            ]);
            return response()->json(['error' => 'Failed to update members'], 500);
        }
    }

    /**
     * Delete a direct message channel (only super admin can delete)
     */
    public function deleteChannel($channelId)
    {
        $current = Auth::guard('admin')->user();
        if (!$current) {
            return response()->json(['errors' => ['auth' => ['Unauthorized']]], 401);
        }

        // Only super admin can delete channels
        if (!$current->is_super) {
            return response()->json(['errors' => ['auth' => ['Only super admin can delete channels']]], 403);
        }

        try {
            $channel = Channel::findOrFail($channelId);

            // Get members before deletion for broadcast
            $memberIds = $channel->users->pluck('id')->toArray();

            // Delete channel (cascades to messages, attachments, channel_user pivot)
            $channel->delete();

            // Broadcast removal to all members
            foreach ($memberIds as $memberId) {
                broadcast(new ChannelMembershipChanged($memberId, $channelId, 'removed'));
            }

            Log::info('Channel deleted', [
                'channel_id' => $channelId,
                'deleted_by' => $current->id,
                'members_count' => count($memberIds)
            ]);

            return response()->json(['success' => true, 'message' => 'Channel deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Channel deletion failed', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
                'admin_id' => $current->id
            ]);
            return response()->json(['error' => 'Failed to delete channel'], 500);
        }
    }

    /**
     * Get thread data for a specific message (parent message + all replies)
     */
    public function getThreadMessages(Message $message)
    {
        $user = Auth::guard('admin')->user();
        if (!$message->channel->users()->where('admin_id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $parentMessage = $message->load(['sender', 'attachments']);

        $replies = Message::where('reply_to_id', $message->id)
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'parent_message' => $parentMessage,
            'replies' => $replies,
        ]);
    }

    /**
     * Post a reply to a thread
     */
    public function postThreadReply(Request $request, Message $message)
    {
        $user = Auth::guard('admin')->user();
        if (!$message->channel->users()->where('admin_id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required_without:attachments|string|max:5000',
            'attachments' => 'array|max:5',
            'attachments.*' => 'file|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $reply = Message::create([
                'channel_id' => $message->channel_id,
                'sender_id' => $user->id,
                'reply_to_id' => $message->id,
                'type' => 'text',
                'body' => $request->body,
                'metadata' => [
                    'reply_preview' => substr($request->body ?? '', 0, 100),
                    'reply_sender' => $user->name,
                ]
            ]);

            // Handle attachments (Simpler version of sendMessage attachment logic)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // $path = $file->store('chat-attachments', 'public');
                    $path = $file->store('uploads/chat-attachments', 'public');
                    $reply->attachments()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            // Update parent's thread count
            $message->increment('thread_count');

            DB::commit();

            $reply->load('sender', 'attachments');

            // Broadcast using existing MessageSent event
            broadcast(new MessageSent($reply))->toOthers();

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'parent_thread_count' => $message->fresh()->thread_count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to post reply'], 500);
        }
    }

    /**
     * Get total unread message count for the authenticated user
     */
    public function getUnreadCount()
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get all channels the user is a member of
        $channels = $user->channels()->pluck('id');

        // Count unread messages across all channels
        $unreadCount = Message::whereIn('channel_id', $channels)
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('reads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Proxy attachment download to bypass CORS and authentication issues
     * Priority: 1) Local file 2) Cloudinary URL
     */
    public function proxyAttachment(MessageAttachment $attachment)
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $meta = $attachment->metadata ?? [];
        $localPath = $meta['local_path'] ?? null;

        // Priority 1: Serve from local storage if available
        if ($localPath) {
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($disk->exists($localPath)) {
                $fullPath = $disk->path($localPath);
                $mimeType = $attachment->mime_type ?? 'application/pdf';

                return response()->file($fullPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $attachment->filename . '"',
                    'Cache-Control' => 'private, max-age=3600',
                ]);
            }
        }

        // Priority 2: Try Cloudinary URL
        $url = $attachment->path ?: $attachment->download_url;
        if (!$url) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => false,
            ]);

            $response = $client->get($url);
            $content = $response->getBody()->getContents();
            $contentType = $response->getHeader('Content-Type')[0] ?? 'application/pdf';

            return response($content, 200)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'inline; filename="' . $attachment->filename . '"')
                ->header('Cache-Control', 'private, max-age=3600');

        } catch (\Exception $e) {
            Log::error('Proxy attachment failed', [
                'attachment_id' => $attachment->id,
                'url' => $url,
                'local_path' => $localPath,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to fetch attachment'], 500);
        }
    }
}
