<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientsExport;

class ClientController extends Controller
{
    /**
     * Display the client dashboard.
     */
    public function index(Request $request)
    {
        $query = Client::withCount('orders');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->input('sort', 'orders_count');
        $sortDir = $request->input('dir', 'desc');
        $allowedColumns = ['name', 'email', 'mobile', 'orders_count', 'created_at'];
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'orders_count';
        }
        $query->orderBy($sortColumn, $sortDir === 'asc' ? 'asc' : 'desc');

        $totalClients = Client::count();
        $clients = $query->paginate(10)->withQueryString();

        return view('clients.index', compact('clients', 'totalClients', 'sortColumn', 'sortDir'));
    }

    /**
     * AJAX endpoint for DataTable.
     */
    public function data(Request $request)
    {
        $query = Client::withCount('orders');

        // Global search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Get counts before pagination
        $total = Client::count();
        $filteredQuery = clone $query;
        $filtered = $filteredQuery->count();

        // Sorting
        $columns = ['name', 'email', 'mobile', 'address', 'tax_id', 'orders_count', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $orderDir = $request->input('order.0.dir', 'desc');
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $data = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    /**
     * Search clients for autocomplete in order form.
     */
    public function search(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $clients = Client::where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->select('id', 'name', 'email', 'mobile', 'address', 'tax_id')
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    /**
     * Show client details with order history.
     */
    public function show(Client $client)
    {
        $client->loadCount('orders');
        $orders = $client->orders()
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('clients.show', compact('client', 'orders'));
    }

    /**
     * Export clients to Excel.
     */
    public function export()
    {
        return Excel::download(new ClientsExport, 'clients_' . date('Y-m-d') . '.xlsx');
    }
}
