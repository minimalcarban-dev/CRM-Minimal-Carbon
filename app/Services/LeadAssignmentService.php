<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Support\Facades\Cache;

class LeadAssignmentService
{
    const STRATEGY_ROUND_ROBIN = 'round_robin';
    const STRATEGY_LOAD_BALANCED = 'load_balanced';
    const STRATEGY_RANDOM = 'random';

    protected string $strategy;

    public function __construct(string $strategy = self::STRATEGY_ROUND_ROBIN)
    {
        $this->strategy = $strategy;
    }

    /**
     * Set assignment strategy
     */
    public function setStrategy(string $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Automatically assign a lead to an available agent
     */
    public function assignLead(Lead $lead, ?int $assignedBy = null): ?Admin
    {
        $availableAgents = $this->getAvailableAgents();

        if ($availableAgents->isEmpty()) {
            return null;
        }

        $selectedAgent = match ($this->strategy) {
            self::STRATEGY_ROUND_ROBIN => $this->roundRobinSelect($availableAgents),
            self::STRATEGY_LOAD_BALANCED => $this->loadBalancedSelect($availableAgents),
            self::STRATEGY_RANDOM => $availableAgents->random(),
            default => $availableAgents->first(),
        };

        if ($selectedAgent) {
            $this->performAssignment($lead, $selectedAgent, $assignedBy);
        }

        return $selectedAgent;
    }

    /**
     * Manually assign a lead to specific agent
     */
    public function assignToAgent(Lead $lead, Admin $agent, ?int $assignedBy = null): void
    {
        $this->performAssignment($lead, $agent, $assignedBy);
    }

    /**
     * Get available agents for assignment
     */
    protected function getAvailableAgents()
    {
        return Admin::query()
            ->where('is_super', false) // Don't auto-assign to super admins
            ->whereHas('permissions', function ($q) {
                $q->where('name', 'leads.view');
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Round-robin selection - cycles through agents in order
     */
    protected function roundRobinSelect($agents): ?Admin
    {
        $lastAssignedId = Cache::get('lead_assignment_last_agent_id', 0);

        // Find next agent in rotation
        $nextAgent = $agents->filter(fn($agent) => $agent->id > $lastAssignedId)->first();

        // If no agent found after last assigned, start from beginning
        if (!$nextAgent) {
            $nextAgent = $agents->first();
        }

        // Cache the selected agent ID for next rotation
        if ($nextAgent) {
            Cache::put('lead_assignment_last_agent_id', $nextAgent->id, now()->addDay());
        }

        return $nextAgent;
    }

    /**
     * Load-balanced selection - assigns to agent with fewest active leads
     */
    protected function loadBalancedSelect($agents): ?Admin
    {
        return $agents->map(function ($agent) {
            $agent->active_lead_count = Lead::where('assigned_to', $agent->id)
                ->whereNotIn('status', ['completed', 'lost'])
                ->count();
            return $agent;
        })->sortBy('active_lead_count')->first();
    }

    /**
     * Perform the actual assignment
     */
    protected function performAssignment(Lead $lead, Admin $agent, ?int $assignedBy): void
    {
        $oldAgent = $lead->assignedAdmin;

        $lead->update(['assigned_to' => $agent->id]);

        // Log the assignment activity
        $description = $oldAgent
            ? "Lead reassigned from {$oldAgent->name} to {$agent->name}"
            : "Lead assigned to {$agent->name}";

        $lead->logActivity(
            LeadActivity::TYPE_ASSIGNED,
            $description,
            $assignedBy ?? auth('admin')->id(),
            [
                'old_agent_id' => $oldAgent?->id,
                'new_agent_id' => $agent->id,
                'strategy' => $this->strategy,
            ]
        );
    }

    /**
     * Unassign a lead
     */
    public function unassignLead(Lead $lead, ?int $adminId = null): void
    {
        $oldAgent = $lead->assignedAdmin;

        $lead->update(['assigned_to' => null]);

        $lead->logActivity(
            LeadActivity::TYPE_UNASSIGNED,
            "Lead unassigned from {$oldAgent?->name}",
            $adminId ?? auth('admin')->id(),
            ['old_agent_id' => $oldAgent?->id]
        );
    }

    /**
     * Bulk assign leads to an agent
     */
    public function bulkAssign(array $leadIds, Admin $agent, ?int $assignedBy = null): int
    {
        $count = 0;
        $leads = Lead::whereIn('id', $leadIds)->get();

        foreach ($leads as $lead) {
            $this->assignToAgent($lead, $agent, $assignedBy);
            $count++;
        }

        return $count;
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStats(): array
    {
        $agents = $this->getAvailableAgents();

        return $agents->map(function ($agent) {
            return [
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
                'total_leads' => Lead::where('assigned_to', $agent->id)->count(),
                'active_leads' => Lead::where('assigned_to', $agent->id)
                    ->whereNotIn('status', ['completed', 'lost'])
                    ->count(),
                'completed_leads' => Lead::where('assigned_to', $agent->id)
                    ->where('status', 'completed')
                    ->count(),
            ];
        })->toArray();
    }
}
