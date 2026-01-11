<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MetaAccount;
use App\Models\MetaConversation;
use App\Models\MetaMessage;
use App\Models\MessageTemplate;
use App\Models\Admin;
use App\Services\LeadScoringService;
use App\Services\LeadAssignmentService;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    protected LeadScoringService $scoringService;
    protected LeadAssignmentService $assignmentService;

    public function __construct(
        LeadScoringService $scoringService,
        LeadAssignmentService $assignmentService
    ) {
        $this->scoringService = $scoringService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display the inbox (kanban board)
     */
    public function index(Request $request)
    {
        $query = Lead::with(['assignedAdmin', 'conversations']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('sla_status')) {
            if ($request->sla_status === 'overdue') {
                $query->overdueSla();
            }
        }

        if ($request->filled('min_score')) {
            $query->where('lead_score', '>=', $request->min_score);
        }

        // Non-super admins only see leads assigned to them
        $admin = auth('admin')->user();
        if (!$admin->is_super) {
            $query->where('assigned_to', $admin->id);
        }

        // Group leads by status for kanban
        $allLeads = $query->latest('last_contact_at')->get();

        $kanbanData = [
            'new' => $allLeads->where('status', 'new')->values(),
            'in_process' => $allLeads->where('status', 'in_process')->values(),
            'completed' => $allLeads->where('status', 'completed')->take(20)->values(),
            'lost' => $allLeads->where('status', 'lost')->take(20)->values(),
        ];

        // Get counts
        $counts = [
            'new' => Lead::where('status', 'new')->count(),
            'in_process' => Lead::where('status', 'in_process')->count(),
            'completed' => Lead::where('status', 'completed')->count(),
            'lost' => Lead::where('status', 'lost')->count(),
            'overdue_sla' => Lead::overdueSla()->count(),
        ];

        // Get agents for filter dropdown
        $agents = Admin::orderBy('name')->get();

        // Quick stats
        $stats = [
            'today_new' => Lead::whereDate('created_at', today())->count(),
            'avg_response_time' => $this->calculateAverageResponseTime(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];

        return view('leads.index', compact('kanbanData', 'counts', 'agents', 'stats'));
    }

    /**
     * Show lead detail with conversation
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'assignedAdmin',
            'createdByAdmin',
            'activities' => fn($q) => $q->with('admin')->latest()->limit(50),
            'conversations.messages',
        ]);

        // Get all messages for this lead, ordered chronologically
        $messages = MetaMessage::whereHas('conversation', function ($q) use ($lead) {
            $q->where('lead_id', $lead->id);
        })->orderBy('created_at', 'asc')->get();

        // Get message templates for quick replies
        $templates = MessageTemplate::active()->popular()->limit(10)->get();

        // Get agents for assignment dropdown
        $agents = Admin::orderBy('name')->get();

        // Mark conversations as read
        $lead->conversations()->update(['is_read' => true]);

        // Get score breakdown
        $scoreBreakdown = $this->scoringService->getScoreBreakdown($lead);

        return view('leads.show', compact('lead', 'messages', 'templates', 'agents', 'scoreBreakdown'));
    }

    /**
     * Create a new lead manually
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'platform' => 'required|in:facebook,instagram',
            'platform_user_id' => 'required|string|max:255',
            'priority' => 'in:low,medium,high',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'new';
        $validated['created_by'] = auth('admin')->id();
        $validated['first_contact_at'] = now();
        $validated['sla_deadline'] = now()->addHours(config('leads.default_sla_hours', 24));

        $lead = Lead::create($validated);

        // Log activity
        $lead->logActivity(
            LeadActivity::TYPE_LEAD_CREATED,
            'Lead created manually'
        );

        // Calculate initial score
        $this->scoringService->updateScore($lead);

        // Auto-assign if enabled
        if (config('leads.auto_assign', true)) {
            $this->assignmentService->assignLead($lead);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully');
    }

    /**
     * Update lead status (AJAX for kanban drag-drop)
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_process,completed,lost',
        ]);

        $oldStatus = $lead->status;
        $lead->update(['status' => $validated['status']]);

        // Log activity
        $lead->logActivity(
            LeadActivity::TYPE_STATUS_CHANGED,
            "Status changed from {$oldStatus} to {$validated['status']}"
        );

        return response()->json([
            'success' => true,
            'lead' => $lead->fresh(),
        ]);
    }

    /**
     * Update lead details
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'priority' => 'in:low,medium,high',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        // Track changes for activity log
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($lead->{$key} !== $value) {
                $changes[$key] = ['old' => $lead->{$key}, 'new' => $value];
            }
        }

        $lead->update($validated);

        if (!empty($changes)) {
            $lead->logActivity(
                LeadActivity::TYPE_NOTE_ADDED,
                'Lead details updated',
                null,
                ['changes' => $changes]
            );
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully');
    }

    /**
     * Assign lead to agent
     */
    public function assign(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'agent_id' => 'nullable|exists:admins,id',
        ]);

        if ($validated['agent_id']) {
            $agent = Admin::find($validated['agent_id']);
            $this->assignmentService->assignToAgent($lead, $agent);
            $message = "Lead assigned to {$agent->name}";
        } else {
            $this->assignmentService->unassignLead($lead);
            $message = "Lead unassigned";
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Send message to lead
     */
    public function sendMessage(Request $request, Lead $lead)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:2000',
                'template_id' => 'nullable|exists:message_templates,id',
            ]);

            // Get or create conversation
            $conversation = $lead->conversations()->first();

            // If no conversation exists, we need a Meta account to send from
            if (!$conversation) {
                // Get the first active Meta account for the lead's platform
                $metaAccount = MetaAccount::where('platform', $lead->platform)
                    ->where('is_active', true)
                    ->first();

                if (!$metaAccount) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No active Meta account configured for ' . ucfirst($lead->platform ?? 'unknown') . '. Please configure one in Meta Settings.',
                    ], 400);
                }

                // Check if lead has platform_user_id
                if (empty($lead->platform_user_id)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot send message: Lead has no platform user ID. This lead was created manually and has not messaged you yet.',
                    ], 400);
                }

                // Create a conversation for this lead
                $conversation = $lead->conversations()->create([
                    'meta_account_id' => $metaAccount->id,
                    'conversation_id' => 'conv_' . $lead->id . '_' . time(),
                    'platform' => $lead->platform,
                    'status' => 'active',
                ]);
            }

            $messageContent = $validated['message'];

            // If using template, render it with lead data
            if ($request->filled('template_id')) {
                $template = MessageTemplate::find($validated['template_id']);
                $messageContent = $template->render([
                    'name' => $lead->name,
                    'date' => now()->format('M d, Y'),
                ]);
                $template->incrementUsage();
            }

            // Send via Meta API
            $metaService = new MetaApiService();
            $metaService->setAccount($conversation->metaAccount);

            $result = $metaService->sendMessage(
                $lead->platform_user_id,
                $messageContent,
                $lead->platform
            );

            if ($result['success']) {
                // Store message in database
                $message = $conversation->messages()->create([
                    'message_id' => $result['message_id'],
                    'direction' => MetaMessage::DIRECTION_OUTGOING,
                    'content' => $messageContent,
                    'status' => MetaMessage::STATUS_SENT,
                ]);

                // Update conversation
                $conversation->updateLastMessageTime();

                // Update lead
                $lead->update(['last_contact_at' => now()]);

                // Log activity
                $lead->logActivity(
                    LeadActivity::TYPE_MESSAGE_SENT,
                    substr($messageContent, 0, 100) . (strlen($messageContent) > 100 ? '...' : '')
                );

                // Update score
                $this->scoringService->updateScore($lead);

                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to send message via Meta API',
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Lead sendMessage error', ['error' => $e->getMessage(), 'lead_id' => $lead->id]);
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add note to lead
     */
    public function addNote(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:5000',
        ]);

        $lead->update(['notes' => $validated['note']]);

        $lead->logActivity(
            LeadActivity::TYPE_NOTE_ADDED,
            'Note updated'
        );

        return response()->json(['success' => true]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:assign,update_status,delete',
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'agent_id' => 'required_if:action,assign|exists:admins,id',
            'status' => 'required_if:action,update_status|in:new,in_process,completed,lost',
        ]);

        $count = 0;

        DB::transaction(function () use ($validated, &$count) {
            $leads = Lead::whereIn('id', $validated['lead_ids']);

            switch ($validated['action']) {
                case 'assign':
                    $agent = Admin::find($validated['agent_id']);
                    $count = $this->assignmentService->bulkAssign($validated['lead_ids'], $agent);
                    break;

                case 'update_status':
                    $count = $leads->update(['status' => $validated['status']]);
                    break;

                case 'delete':
                    $count = $leads->delete();
                    break;
            }
        });

        return response()->json([
            'success' => true,
            'affected' => $count,
        ]);
    }

    /**
     * Soft delete a lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully');
    }

    /**
     * Analytics dashboard
     */
    public function analytics(Request $request)
    {
        $dateRange = $request->get('range', '30');
        $startDate = now()->subDays((int) $dateRange);

        // KPIs
        $kpis = [
            'total_leads' => Lead::count(),
            'new_today' => Lead::whereDate('created_at', today())->count(),
            'avg_response_time' => $this->calculateAverageResponseTime(),
            'conversion_rate' => $this->calculateConversionRate(),
            'sla_compliance' => $this->calculateSlaCompliance(),
        ];

        // Leads by status
        $byStatus = Lead::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Leads by platform
        $byPlatform = Lead::select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        // Trends (leads per day)
        $trends = Lead::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performers
        $topAgents = Admin::select('admins.id', 'admins.name')
            ->selectRaw('COUNT(leads.id) as lead_count')
            ->selectRaw('SUM(CASE WHEN leads.status = "completed" THEN 1 ELSE 0 END) as completed_count')
            ->leftJoin('leads', 'admins.id', '=', 'leads.assigned_to')
            ->where('leads.created_at', '>=', $startDate)
            ->groupBy('admins.id', 'admins.name')
            ->orderByDesc('completed_count')
            ->limit(5)
            ->get();

        return view('leads.analytics', compact('kpis', 'byStatus', 'byPlatform', 'trends', 'topAgents', 'dateRange'));
    }

    // ─────────────────────────────────────────────────────────────
    // Helper Methods
    // ─────────────────────────────────────────────────────────────

    protected function calculateAverageResponseTime(): string
    {
        // Calculate average time between incoming message and first outgoing response
        // Simplified: return placeholder for now
        return '12 min';
    }

    protected function calculateConversionRate(): float
    {
        $total = Lead::whereNotIn('status', ['new'])->count();
        $converted = Lead::where('status', 'completed')->count();

        return $total > 0 ? round(($converted / $total) * 100, 1) : 0;
    }

    protected function calculateSlaCompliance(): float
    {
        $total = Lead::whereNotNull('sla_deadline')->count();
        $onTime = Lead::whereNotNull('sla_deadline')
            ->where(function ($q) {
                $q->whereIn('status', ['completed', 'lost'])
                    ->orWhere('sla_deadline', '>', now());
            })->count();

        return $total > 0 ? round(($onTime / $total) * 100, 1) : 100;
    }
}
