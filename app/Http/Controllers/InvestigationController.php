<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderInvestigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestigationController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderInvestigation::with(['order', 'creator'])->latest();

        if ($request->filled('status')) {
            $query->where('investigation_status', $request->status);
        }

        $investigations = $query->paginate(20);

        return view('investigations.index', compact('investigations'));
    }

    public function fragment(OrderInvestigation $investigation)
    {
        $investigation->load(['order', 'creator']);
        return view('investigations.details', compact('investigation'))->render();
    }

    public function start(Order $order)
    {
        if (!$order->tracking_number) {
            return response()->json(['success' => false, 'message' => 'Order must have a tracking number to start an investigation.'], 400);
        }

        // Check for existing active investigation
        $existing = OrderInvestigation::where('order_id', $order->id)
            ->whereNotIn('investigation_status', ['Resolved', 'Delivered'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'An investigation is already active for this order.'], 400);
        }

        $investigation = OrderInvestigation::create([
            'order_id' => $order->id,
            'created_by' => Auth::guard('admin')->id(),
            'customer_name' => $order->client_name,
            'courier_name' => $order->shipping_company_name,
            'tracking_number' => $order->tracking_number,
            'shipment_status' => $order->tracking_status,
            'investigation_status' => 'Pending',
            'last_tracking_update' => now(),
            'investigation_notes' => [
                [
                    'time' => now()->toDateTimeString(),
                    'admin' => Auth::guard('admin')->user()->name,
                    'text' => 'Investigation started manually from orders panel.'
                ]
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Investigation started successfully.',
            'investigation' => $investigation
        ]);
    }

    public function addNote(Request $request, OrderInvestigation $investigation)
    {
        $request->validate([
            'note' => 'required|string|max:5000'
        ]);

        $notes = $investigation->investigation_notes ?? [];
        $notes[] = [
            'time' => now()->toDateTimeString(),
            'admin' => Auth::guard('admin')->user()->name,
            'text' => $request->note
        ];

        $investigation->update(['investigation_notes' => $notes]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Note added successfully.', 'notes' => $notes]);
        }

        return back()->with('success', 'Note added successfully.');
    }

    public function updateStatus(Request $request, OrderInvestigation $investigation)
    {
        $request->validate([
            'status' => 'required|string|in:Pending,In Progress,Carrier Contacted,Resolved,Delivered'
        ]);

        $investigation->update(['investigation_status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $request->status]);
        }

        return back()->with('success', 'Status updated successfully.');
    }
}
