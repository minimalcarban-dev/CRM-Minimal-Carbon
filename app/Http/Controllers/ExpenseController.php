<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     */
    public function index(Request $request)
    {
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

        // Current month summary
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyIncome = Expense::forMonth($currentYear, $currentMonth)->income()->sum('amount');
        $monthlyExpense = Expense::forMonth($currentYear, $currentMonth)->expense()->sum('amount');
        $monthlyBalance = $monthlyIncome - $monthlyExpense;

        // All time totals
        $totalIncome = Expense::income()->sum('amount');
        $totalExpense = Expense::expense()->sum('amount');
        $totalBalance = $totalIncome - $totalExpense;

        return view('expenses.index', compact(
            'expenses',
            'monthlyIncome',
            'monthlyExpense',
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

        return view('expenses.create', compact('incomeCategories', 'expenseCategories', 'paymentMethods'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:in,out',
            'category' => 'required|string|max:100',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'paid_to_received_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['admin_id'] = Auth::guard('admin')->id();

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

        return view('expenses.edit', compact('expense', 'incomeCategories', 'expenseCategories', 'paymentMethods'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:in,out',
            'category' => 'required|string|max:100',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'paid_to_received_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

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
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $expenses = Expense::forMonth($year, $month)->get();

        // Summary
        $totalIncome = $expenses->where('transaction_type', 'in')->sum('amount');
        $totalExpense = $expenses->where('transaction_type', 'out')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Category breakdown for income
        $incomeByCategory = $expenses->where('transaction_type', 'in')
            ->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        // Category breakdown for expense
        $expenseByCategory = $expenses->where('transaction_type', 'out')
            ->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        return view('expenses.monthly-report', compact(
            'year',
            'month',
            'expenses',
            'totalIncome',
            'totalExpense',
            'balance',
            'incomeByCategory',
            'expenseByCategory'
        ));
    }

    /**
     * Annual Report View
     */
    public function annualReport(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $income = Expense::forMonth($year, $m)->income()->sum('amount');
            $expense = Expense::forMonth($year, $m)->expense()->sum('amount');
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
     */
    public function exportMonthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

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
            fputcsv($file, ['Date', 'Title', 'Category', 'In Amount', 'Out Amount', 'Payment Method', 'Reference', 'Notes']);

            foreach ($expenses as $exp) {
                fputcsv($file, [
                    $exp->date->format('Y-m-d'),
                    $exp->title,
                    $exp->category_name,
                    $exp->transaction_type === 'in' ? $exp->amount : '',
                    $exp->transaction_type === 'out' ? $exp->amount : '',
                    Expense::PAYMENT_METHODS[$exp->payment_method] ?? $exp->payment_method,
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

            $incomeRow = ['Income'];
            $expenseRow = ['Expense'];
            $cashflowRow = ['Cash Flow'];
            $totalIncome = 0;
            $totalExpense = 0;

            for ($m = 1; $m <= 12; $m++) {
                $income = Expense::forMonth($year, $m)->income()->sum('amount');
                $expense = Expense::forMonth($year, $m)->expense()->sum('amount');
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
