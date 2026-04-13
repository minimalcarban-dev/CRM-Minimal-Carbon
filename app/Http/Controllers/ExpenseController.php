<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Party;
use App\Services\CloudinaryUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    private CloudinaryUploadService $uploadService;

    public function __construct(CloudinaryUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    /**
     * Display a listing of the expenses.
     * 
     * NOTE: Shows ALL transactions regardless of payment method,
     * but balance calculations only include CASH transactions.
     */
    public function index(Request $request)
    {
        // Build query - SHOW ALL TRANSACTIONS (no payment method filter)
        $query = Expense::with('admin')->latest('date');

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->paginate(20)->withQueryString();

        // ==========================================
        // BALANCE CALCULATIONS - CASH ONLY
        // ==========================================

        // Current month summary (CASH ONLY)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $cashSummary = Expense::getCashSummaryForMonth($currentYear, $currentMonth);

        $monthlyIncome = $cashSummary['monthly_income'];
        $monthlyExpense = $cashSummary['monthly_expense'];
        $monthlyCashflow = $cashSummary['monthly_cashflow'];
        $openingBalance = $cashSummary['opening_balance'];
        $monthlyBalance = $cashSummary['closing_balance'];

        // All time totals (CASH ONLY)
        $totalIncome = Expense::income()
            ->cash()  // ← CASH ONLY
            ->sum('amount');

        $totalExpense = Expense::expense()
            ->cash()  // ← CASH ONLY
            ->sum('amount');

        $totalBalance = $totalIncome - $totalExpense;

        return view('expenses.index', compact(
            'expenses',
            'monthlyIncome',
            'monthlyExpense',
            'monthlyCashflow',
            'openingBalance',
            'monthlyBalance',
            'totalIncome',
            'totalExpense',
            'totalBalance'
        ));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        $incomeCategories = Expense::INCOME_CATEGORIES;
        $expenseCategories = Expense::EXPENSE_CATEGORIES;
        $paymentMethods = Expense::PAYMENT_METHODS;

        // Load only Banks and In Person category parties
        $parties = Party::byCategories([
            Party::CATEGORY_BANKS,
            Party::CATEGORY_IN_PERSON
        ])->orderBy('name')->get(['id', 'name', 'phone', 'email', 'category']);

        return view('expenses.create', compact('incomeCategories', 'expenseCategories', 'paymentMethods', 'parties'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $validated = $request->validated();

        $validated['admin_id'] = Auth::guard('admin')->id();

        // Handle invoice image upload to Cloudinary using direct SDK
        if ($request->hasFile('invoice_image')) {
            try {
                $uploadedFiles = $this->uploadService->uploadFromRequest($request, 'invoice_image', 'invoices/expenses', 1);
                if (!empty($uploadedFiles)) {
                    $validated['invoice_image'] = $uploadedFiles[0];
                } else {
                    unset($validated['invoice_image']);
                }
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed for expense: ' . $e->getMessage(), [
                    'expense_title' => $validated['title'] ?? 'N/A',
                    'exception' => $e
                ]);
                unset($validated['invoice_image']);
            }
        }

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Transaction recorded successfully!');
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        $expense->load('admin');
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        $incomeCategories = Expense::INCOME_CATEGORIES;
        $expenseCategories = Expense::EXPENSE_CATEGORIES;
        $paymentMethods = Expense::PAYMENT_METHODS;

        // Load only Banks and In Person category parties
        $parties = Party::byCategories([
            Party::CATEGORY_BANKS,
            Party::CATEGORY_IN_PERSON
        ])->orderBy('name')->get(['id', 'name', 'phone', 'email', 'category']);

        return view('expenses.edit', compact('expense', 'incomeCategories', 'expenseCategories', 'paymentMethods', 'parties'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(\App\Http\Requests\UpdateExpenseRequest $request, Expense $expense)
    {
        $validated = $request->validated();

        // Handle invoice image
        if ($request->input('remove_invoice_image') && $expense->invoice_image_public_id) {
            try {
                $this->uploadService->delete($expense->invoice_image_public_id);
            } catch (\Exception $e) {
                Log::error('Cloudinary delete failed: ' . $e->getMessage());
            }
            $validated['invoice_image'] = null;
        } elseif ($request->hasFile('invoice_image')) {
            // Delete old image if exists
            if ($expense->invoice_image_public_id) {
                try {
                    $this->uploadService->delete($expense->invoice_image_public_id);
                } catch (\Exception $e) {
                    Log::error('Cloudinary delete failed: ' . $e->getMessage());
                }
            }

            try {
                $uploadedFiles = $this->uploadService->uploadFromRequest($request, 'invoice_image', 'invoices/expenses', 1);
                if (!empty($uploadedFiles)) {
                    $validated['invoice_image'] = $uploadedFiles[0];
                } else {
                    unset($validated['invoice_image']);
                }
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed for expense update: ' . $e->getMessage(), [
                    'expense_id' => $expense->id,
                    'exception' => $e
                ]);
                unset($validated['invoice_image']);
            }
        } else {
            unset($validated['invoice_image']);
        }
        unset($validated['remove_invoice_image']);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Monthly Report View
     * 
     * NOTE: Category breakdowns and summaries are CASH ONLY
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Get ALL expenses for display
        $expenses = Expense::forMonth($year, $month)->get();
        $cashSummary = Expense::getCashSummaryForMonth((int) $year, (int) $month);

        // ==========================================
        // SUMMARY CALCULATIONS - CASH ONLY
        // ==========================================
        $totalIncome = $cashSummary['monthly_income'];
        $totalExpense = $cashSummary['monthly_expense'];
        $openingBalance = $cashSummary['opening_balance'];
        $monthlyCashflow = $cashSummary['monthly_cashflow'];
        $balance = $cashSummary['closing_balance'];

        // Category breakdown for income (CASH ONLY)
        $incomeByCategory = $expenses
            ->where('transaction_type', 'in')
            ->where('payment_method', 'cash')  // ← CASH ONLY
            ->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        // Category breakdown for expense (CASH ONLY)
        $expenseByCategory = $expenses
            ->where('transaction_type', 'out')
            ->where('payment_method', 'cash')  // ← CASH ONLY
            ->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        return view('expenses.monthly-report', compact(
            'year',
            'month',
            'expenses',
            'totalIncome',
            'totalExpense',
            'openingBalance',
            'monthlyCashflow',
            'balance',
            'incomeByCategory',
            'expenseByCategory'
        ));
    }

    /**
     * Annual Report View
     * 
     * NOTE: All calculations are CASH ONLY
     */
    public function annualReport(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            // CASH ONLY for all calculations
            $income = Expense::forMonth($year, $m)
                ->income()
                ->cash()  // ← CASH ONLY
                ->sum('amount');

            $expense = Expense::forMonth($year, $m)
                ->expense()
                ->cash()  // ← CASH ONLY
                ->sum('amount');

            $monthlyData[$m] = [
                'income' => $income,
                'expense' => $expense,
                'cashflow' => $income - $expense,
            ];
        }

        $totals = [
            'income' => array_sum(array_column($monthlyData, 'income')),
            'expense' => array_sum(array_column($monthlyData, 'expense')),
            'cashflow' => array_sum(array_column($monthlyData, 'cashflow')),
        ];

        $averages = [
            'income' => $totals['income'] / 12,
            'expense' => $totals['expense'] / 12,
            'cashflow' => $totals['cashflow'] / 12,
        ];

        return view('expenses.annual-report', compact('year', 'monthlyData', 'totals', 'averages'));
    }

    /**
     * Export Monthly Report to Excel
     * 
     * NOTE: Exports ALL transactions but marks which are CASH
     */
    public function exportMonthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Get ALL expenses (not just cash)
        $expenses = Expense::forMonth($year, $month)
            ->orderBy('date')
            ->get();

        $filename = "expenses_{$year}_{$month}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');

            // Added "Affects Cash Balance" column
            fputcsv($file, [
                'Date',
                'Title',
                'Category',
                'In Amount',
                'Out Amount',
                'Payment Method',
                'Affects Cash Balance',  // NEW COLUMN
                'Reference',
                'Notes'
            ]);

            foreach ($expenses as $exp) {
                fputcsv($file, [
                    $exp->date->format('Y-m-d'),
                    $exp->title,
                    $exp->category_name,
                    $exp->transaction_type === 'in' ? $exp->amount : '',
                    $exp->transaction_type === 'out' ? $exp->amount : '',
                    Expense::PAYMENT_METHODS[$exp->payment_method] ?? $exp->payment_method,
                    $exp->payment_method === 'cash' ? 'Yes' : 'No',  // NEW FIELD
                    $exp->reference_number,
                    $exp->notes,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Annual Report to Excel
     * 
     * NOTE: Calculations are CASH ONLY
     */
    public function exportAnnual(Request $request)
    {
        $year = $request->get('year', now()->year);

        $filename = "annual_report_{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($year) {
            $file = fopen('php://output', 'w');

            // Header row with months
            $header = ['Category', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Total', 'Average'];
            fputcsv($file, $header);

            $incomeRow = ['Income (Cash Only)'];  // Clarified in label
            $expenseRow = ['Expense (Cash Only)'];  // Clarified in label
            $cashflowRow = ['Cash Flow'];
            $totalIncome = 0;
            $totalExpense = 0;

            for ($m = 1; $m <= 12; $m++) {
                // CASH ONLY for calculations
                $income = Expense::forMonth($year, $m)
                    ->income()
                    ->cash()  // ← CASH ONLY
                    ->sum('amount');

                $expense = Expense::forMonth($year, $m)
                    ->expense()
                    ->cash()  // ← CASH ONLY
                    ->sum('amount');

                $incomeRow[] = $income;
                $expenseRow[] = $expense;
                $cashflowRow[] = $income - $expense;
                $totalIncome += $income;
                $totalExpense += $expense;
            }

            $incomeRow[] = $totalIncome;
            $incomeRow[] = round($totalIncome / 12, 2);
            $expenseRow[] = $totalExpense;
            $expenseRow[] = round($totalExpense / 12, 2);
            $cashflowRow[] = $totalIncome - $totalExpense;
            $cashflowRow[] = round(($totalIncome - $totalExpense) / 12, 2);

            fputcsv($file, $incomeRow);
            fputcsv($file, $expenseRow);
            fputcsv($file, $cashflowRow);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
