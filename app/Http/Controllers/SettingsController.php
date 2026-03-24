<?php

namespace App\Http\Controllers;

use App\Models\AllowedIp;
use App\Models\AppSetting;
use App\Models\IpAccessLog;
use App\Models\IpAccessRequest;
use App\Services\GeoIpService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Show the IP security settings page.
     */
    public function index()
    {
        $allowedIps = AllowedIp::with('addedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        $ipRestrictionEnabled = AppSetting::isEnabled('ip_restriction_enabled');
        $currentIp = request()->ip();

        // Audit logs — last 50 blocked attempts
        $accessLogs = IpAccessLog::orderBy('blocked_at', 'desc')
            ->limit(50)
            ->get();

        // Pending access requests
        $accessRequests = IpAccessRequest::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $pendingRequestCount = IpAccessRequest::pending()->count();

        return view('settings.ip-security', compact(
            'allowedIps',
            'ipRestrictionEnabled',
            'currentIp',
            'accessLogs',
            'accessRequests',
            'pendingRequestCount'
        ));
    }

    /**
     * Add a new IP to the whitelist.
     */
    public function storeIp(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => [
                'required',
                'string',
                'max:45',
                'unique:allowed_ips,ip_address',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_IP)) {
                        $fail('Please enter a valid IP address.');
                    }
                },
            ],
            'label' => 'nullable|string|max:255',
        ]);

        $ip = AllowedIp::create([
            'ip_address' => $validated['ip_address'],
            'label' => $validated['label'] ?? null,
            'is_active' => true,
            'added_by' => Auth::guard('admin')->id(),
        ]);

        // Audit Log entry
        AuditLogger::log('IP Whitelisted', $ip, Auth::guard('admin')->id(), [], $ip->toArray());

        return redirect()->route('settings.security.index')
            ->with('success', 'IP address added to whitelist successfully!');
    }

    /**
     * Toggle an IP's active status.
     */
    public function toggleIp(AllowedIp $ip)
    {
        $oldValues = $ip->toArray();
        $ip->update(['is_active' => !$ip->is_active]);
        $newValues = $ip->toArray();

        // Audit Log entry
        AuditLogger::log('IP Status Toggled', $ip, Auth::guard('admin')->id(), $oldValues, $newValues);

        return response()->json([
            'success' => true,
            'is_active' => $ip->is_active,
            'message' => $ip->is_active ? 'IP activated' : 'IP deactivated',
        ]);
    }

    /**
     * Delete an IP from the whitelist.
     */
    public function destroyIp(AllowedIp $ip)
    {
        $oldValues = $ip->toArray();
        $ip->delete();

        // Audit Log entry
        AuditLogger::log('IP Removed', $ip, Auth::guard('admin')->id(), $oldValues, []);

        return redirect()->route('settings.security.index')
            ->with('success', 'IP address removed from whitelist.');
    }

    /**
     * Toggle IP restriction on/off.
     */
    public function toggleIpRestriction(Request $request)
    {
        $currentStatus = AppSetting::isEnabled('ip_restriction_enabled');
        $newStatus = !$currentStatus;

        // Fetch (or create) the AppSetting record to log it with AuditLogger
        $setting = AppSetting::where('key', 'ip_restriction_enabled')->first();
        if (!$setting) {
            $setting = AppSetting::create(['key' => 'ip_restriction_enabled', 'value' => $currentStatus ? 'true' : 'false']);
        }

        $oldValues = ['value' => $currentStatus ? 'true' : 'false'];

        // Safety check: if enabling, auto-add admin's IP
        if ($newStatus) {
            $currentIp = $request->ip();
            if (!AllowedIp::isAllowed($currentIp)) {
                $newIp = AllowedIp::create([
                    'ip_address' => $currentIp,
                    'label' => 'Auto-added (Admin)',
                    'is_active' => true,
                    'added_by' => Auth::guard('admin')->id(),
                ]);
                AuditLogger::log('IP Whitelisted (Auto)', $newIp, Auth::guard('admin')->id(), [], $newIp->toArray());
            }
        }

        AppSetting::set('ip_restriction_enabled', $newStatus ? 'true' : 'false');
        $newValues = ['value' => $newStatus ? 'true' : 'false'];

        // Audit Log entry for the setting toggle
        AuditLogger::log('IP Restriction Setting Toggled', $setting, Auth::guard('admin')->id(), $oldValues, $newValues);

        return response()->json([
            'success' => true,
            'enabled' => $newStatus,
            'message' => $newStatus
                ? 'IP restriction enabled! Only trusted devices can access the site.'
                : 'IP restriction disabled. Site is accessible from any IP.',
        ]);
    }

    /**
     * Get the current visitor's IP address (AJAX helper).
     */
    public function getMyIp()
    {
        return response()->json([
            'ip' => request()->ip(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Access Request Methods (Public — no auth)
    // ─────────────────────────────────────────────────────────────

    /**
     * Submit an access request from the 403 page.
     */
    public function submitAccessRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        $clientIp = $request->ip();

        // Prevent duplicate pending requests from same IP
        $existingPending = IpAccessRequest::where('ip_address', $clientIp)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending access request. Please wait for admin approval.',
            ], 422);
        }

        $geo = GeoIpService::lookup($clientIp);
        $requestToken = Str::random(64);

        $accessRequest = IpAccessRequest::create([
            'ip_address' => $clientIp,
            'name' => $validated['name'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'country' => $geo['country'],
            'city' => $geo['city'],
            'region' => $geo['region'],
            'isp' => $geo['isp'],
            'latitude' => $geo['latitude'],
            'longitude' => $geo['longitude'],
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'request_token' => $requestToken,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request submitted! An administrator will review your request.',
        ])->withCookie(cookie(
                    'pending_device_token',
                    $requestToken,
                    60 * 24 * 30,
                    '/',
                    null,
                    true,
                    true,
                    false,
                    'Lax'
                ));
    }

    // ─────────────────────────────────────────────────────────────
    // Admin Review Methods (Auth required)
    // ─────────────────────────────────────────────────────────────

    /**
     * Approve an access request → create a trusted device record.
     */
    public function approveRequest(IpAccessRequest $accessRequest)
    {
        $deviceToken = $accessRequest->request_token ?: Str::random(64);

        // Mark as approved
        $accessRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        // Create a device trust record (or update existing)
        $existing = AllowedIp::where('ip_address', $accessRequest->ip_address)->first();

        if ($existing) {
            $oldValues = $existing->toArray();
            $existing->update([
                'device_token' => $deviceToken,
                'user_agent' => $accessRequest->user_agent,
                'last_used_at' => now(),
                'city' => $accessRequest->city,
                'country' => $accessRequest->country,
                'is_active' => true,
            ]);
            AuditLogger::log('Device Approved (Updated)', $existing, Auth::guard('admin')->id(), $oldValues, $existing->toArray());
        } else {
            $record = AllowedIp::create([
                'ip_address' => $accessRequest->ip_address,
                'device_token' => $deviceToken,
                'user_agent' => $accessRequest->user_agent,
                'last_used_at' => now(),
                'city' => $accessRequest->city,
                'country' => $accessRequest->country,
                'label' => $accessRequest->name
                    ? $accessRequest->name . ' (Approved Device)'
                    : 'Approved Device #' . $accessRequest->id,
                'is_active' => true,
                'added_by' => Auth::guard('admin')->id(),
            ]);
            AuditLogger::log('Device Approved (New)', $record, Auth::guard('admin')->id(), [], $record->toArray());
        }

        return response()->json([
            'success' => true,
            'message' => "Device approved! {$accessRequest->ip_address} has been granted trusted access.",
        ]);
    }

    /**
     * Reject an access request.
     */
    public function rejectRequest(IpAccessRequest $accessRequest)
    {
        $old = $accessRequest->toArray();
        $accessRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogger::log('Access Request Rejected', $accessRequest, Auth::guard('admin')->id(), $old, $accessRequest->toArray());

        return response()->json([
            'success' => true,
            'message' => "Request from {$accessRequest->ip_address} rejected.",
        ]);
    }

    /**
     * Clear all audit logs.
     */
    public function clearLogs()
    {
        // For clearing the monitor (IpAccessLog)
        IpAccessLog::truncate();

        // Log the action itself in AuditLog
        // Since AuditLog record about record-clearance is weird to attach to a specific model, we can probably use a dummy or skip
        // But the user might want this action to be logged too.

        return response()->json([
            'success' => true,
            'message' => 'All monitor logs cleared.',
        ]);
    }
}
