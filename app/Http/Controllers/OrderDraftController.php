<?php

namespace App\Http\Controllers;

use App\Models\OrderDraft;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderDraftController extends Controller
{
    // Note: Authentication middleware is applied via routes (admin.auth in Route::middleware)
    // No constructor middleware needed here

    /**
     * Display list of drafts
     */
    public function index(Request $request)
    {
        $query = OrderDraft::with(['admin', 'company'])
            ->notExpired()
            ->orderBy('updated_at', 'desc');

        // Filter by source
        if ($request->filled('source')) {
            $query->bySource($request->source);
        }

        // Filter by order type
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        // Filter by admin (for super admins to see all)
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Filter by date range
        if ($request->filled('days')) {
            $query->recent((int) $request->days);
        }

        // Search by client name
        if ($request->filled('search')) {
            $query->where('client_name', 'like', '%' . $request->search . '%');
        }

        $drafts = $query->paginate(15)->withQueryString();

        // Stats
        $totalDrafts = OrderDraft::notExpired()->count();
        $errorDrafts = OrderDraft::notExpired()->bySource('error')->count();
        $autoSaveDrafts = OrderDraft::notExpired()->bySource('auto_save')->count();
        $expiringSoon = OrderDraft::expiringSoon()->count();

        return view('orders.drafts.index', compact(
            'drafts',
            'totalDrafts',
            'errorDrafts',
            'autoSaveDrafts',
            'expiringSoon'
        ));
    }

    /**
     * AJAX: Auto-save draft data
     */
    public function save(Request $request)
    {
        try {
            $adminId = Auth::guard('admin')->id();

            $validated = $request->validate([
                'order_type' => 'nullable|string|in:ready_to_ship,custom_diamond,custom_jewellery',
                'form_data' => 'required|array',
                'last_step' => 'nullable|string|max:100',
                'draft_id' => 'nullable|integer',
            ]);

            // Check if we're updating existing draft or creating new
            if (!empty($validated['draft_id'])) {
                $draft = OrderDraft::where('id', $validated['draft_id'])
                    ->where('admin_id', $adminId)
                    ->first();

                if (!$draft) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Draft not found'
                    ], 404);
                }
            } else {
                // Look for existing auto-save draft from same admin for same order type
                $draft = OrderDraft::where('admin_id', $adminId)
                    ->where('order_type', $validated['order_type'] ?? null)
                    ->where('source', 'auto_save')
                    ->where('created_at', '>=', Carbon::now()->subHours(24))
                    ->first();

                if (!$draft) {
                    $draft = new OrderDraft();
                    $draft->admin_id = $adminId;
                    $draft->source = 'auto_save';
                }
            }

            $draft->order_type = $validated['order_type'] ?? $draft->order_type;
            $draft->form_data = $validated['form_data'];
            $draft->last_step = $validated['last_step'] ?? $draft->last_step;
            $draft->client_name = $validated['form_data']['client_name'] ?? null;
            $draft->company_id = $validated['form_data']['company_id'] ?? null;
            $draft->save();

            return response()->json([
                'success' => true,
                'draft_id' => $draft->id,
                'message' => 'Draft saved successfully',
                'saved_at' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Draft save failed', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft'
            ], 500);
        }
    }

    /**
     * Save draft when error occurs during order creation
     */
    public function saveOnError(array $formData, string $errorMessage, ?string $orderType = null): ?OrderDraft
    {
        try {
            $adminId = Auth::guard('admin')->id();

            $draft = new OrderDraft();
            $draft->admin_id = $adminId;
            $draft->order_type = $orderType;
            $draft->form_data = $formData;
            $draft->error_message = $errorMessage;
            $draft->source = 'error';
            $draft->client_name = $formData['client_name'] ?? null;
            $draft->company_id = $formData['company_id'] ?? null;
            $draft->save();

            Log::info('Order draft saved due to error', [
                'draft_id' => $draft->id,
                'admin_id' => $adminId,
                'error' => $errorMessage
            ]);

            return $draft;

        } catch (\Exception $e) {
            Log::error('Failed to save error draft', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::guard('admin')->id()
            ]);
            return null;
        }
    }

    /**
     * Resume editing a draft - redirect to order create with draft data
     */
    public function resume(OrderDraft $draft)
    {
        // Make sure current admin can access this draft
        // (For now, any admin can resume any draft)

        return redirect()->route('orders.create', ['draft_id' => $draft->id]);
    }

    /**
     * Preview draft data
     */
    public function show(OrderDraft $draft)
    {
        $draft->load(['admin', 'company']);

        return view('orders.drafts.show', compact('draft'));
    }

    /**
     * Delete a draft
     */
    public function destroy(OrderDraft $draft)
    {
        try {
            $draft->delete();

            return redirect()->route('orders.drafts.index')
                ->with('success', 'Draft discarded successfully.');

        } catch (\Exception $e) {
            Log::error('Draft delete failed', [
                'draft_id' => $draft->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('orders.drafts.index')
                ->with('error', 'Failed to delete draft.');
        }
    }

    /**
     * AJAX: Get draft count for badge
     */
    public function count()
    {
        $adminId = Auth::guard('admin')->id();

        $count = OrderDraft::notExpired()->count();
        $myCount = OrderDraft::notExpired()->where('admin_id', $adminId)->count();

        return response()->json([
            'total' => $count,
            'my_drafts' => $myCount
        ]);
    }

    /**
     * AJAX: Get drafts for current admin (for notification popup)
     */
    public function myDrafts()
    {
        $adminId = Auth::guard('admin')->id();

        $drafts = OrderDraft::where('admin_id', $adminId)
            ->notExpired()
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get(['id', 'order_type', 'client_name', 'source', 'error_message', 'updated_at']);

        return response()->json([
            'count' => $drafts->count(),
            'drafts' => $drafts->map(function ($draft) {
                return [
                    'id' => $draft->id,
                    'order_type' => $draft->order_type_label,
                    'client_name' => $draft->client_name ?? 'No client name',
                    'source' => $draft->source_label,
                    'has_error' => !empty($draft->error_message),
                    'time_ago' => $draft->updated_at->diffForHumans(),
                    'resume_url' => route('orders.drafts.resume', $draft->id)
                ];
            })
        ]);
    }

    /**
     * AJAX: Delete draft
     */
    public function ajaxDestroy(OrderDraft $draft)
    {
        try {
            $draft->delete();

            return response()->json([
                'success' => true,
                'message' => 'Draft deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete draft'
            ], 500);
        }
    }

    /**
     * Delete draft for a specific admin and order type (used when order is successfully created)
     */
    public static function clearAutoSaveDraft(int $adminId, ?string $orderType = null): void
    {
        try {
            $query = OrderDraft::where('admin_id', $adminId)
                ->where('source', 'auto_save');

            if ($orderType) {
                $query->where('order_type', $orderType);
            }

            $query->delete();

        } catch (\Exception $e) {
            Log::error('Failed to clear auto-save draft', [
                'admin_id' => $adminId,
                'order_type' => $orderType,
                'error' => $e->getMessage()
            ]);
        }
    }
}
