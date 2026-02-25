<?php

namespace App\Http\Controllers;

use App\Models\AllowedIp;
use App\Models\AppSetting;
use App\Models\IpAccessLog;
use App\Models\IpAccessRequest;
use App\Services\GeoIpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        AllowedIp::create([
            'ip_address' => $validated['ip_address'],
            'label' => $validated['label'] ?? null,
            'is_active' => true,
            'added_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('settings.security.index')
            ->with('success', 'IP address added to whitelist successfully!');
    }

    /**
     * Toggle an IP's active status.
     */
    public function toggleIp(AllowedIp $ip)
    {
        $ip->update(['is_active' => !$ip->is_active]);

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
        $ip->delete();

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

        // Safety check: if enabling, auto-add admin's IP
        if ($newStatus) {
            $currentIp = $request->ip();
            if (!AllowedIp::isAllowed($currentIp)) {
                AllowedIp::create([
                    'ip_address' => $currentIp,
                    'label' => 'Auto-added (Admin)',
                    'is_active' => true,
                    'added_by' => Auth::guard('admin')->id(),
                ]);
            }
        }

        AppSetting::set('ip_restriction_enabled', $newStatus ? 'true' : 'false');

        return response()->json([
            'success' => true,
            'enabled' => $newStatus,
            'message' => $newStatus
                ? 'IP restriction enabled! Only whitelisted IPs can access the site.'
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

        IpAccessRequest::create([
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
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request submitted! An administrator will review your request.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Admin Review Methods (Auth required)
    // ─────────────────────────────────────────────────────────────

    /**
     * Approve an access request → auto-add IP to whitelist.
     */
    public function approveRequest(IpAccessRequest $accessRequest)
    {
        // Mark as approved
        $accessRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        // Auto-add IP to whitelist if not already there
        $existing = AllowedIp::where('ip_address', $accessRequest->ip_address)->first();
        if (!$existing) {
            AllowedIp::create([
                'ip_address' => $accessRequest->ip_address,
                'label' => $accessRequest->name
                    ? $accessRequest->name . ' (Approved Request)'
                    : 'Approved Request #' . $accessRequest->id,
                'is_active' => true,
                'added_by' => Auth::guard('admin')->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Request approved! IP {$accessRequest->ip_address} added to whitelist.",
        ]);
    }

    /**
     * Reject an access request.
     */
    public function rejectRequest(IpAccessRequest $accessRequest)
    {
        $accessRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

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
        IpAccessLog::truncate();

        return response()->json([
            'success' => true,
            'message' => 'All audit logs cleared.',
        ]);
    }
}
