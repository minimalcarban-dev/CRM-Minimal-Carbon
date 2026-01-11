<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = 1; // Default admin

        // Sample Income transactions
        $incomes = [
            ['date' => '2026-01-02', 'title' => 'Miteshbhai Payment', 'amount' => 500000, 'category' => 'miteshbhai_in', 'payment_method' => 'cash', 'paid_to_received_from' => 'Miteshbhai'],
            ['date' => '2026-01-05', 'title' => 'Vijaybhai Order Payment', 'amount' => 350000, 'category' => 'vijaybhai_in', 'payment_method' => 'upi', 'paid_to_received_from' => 'Vijaybhai'],
            ['date' => '2026-01-08', 'title' => 'Chithi Received', 'amount' => 75000, 'category' => 'chithi_in', 'payment_method' => 'cash', 'paid_to_received_from' => 'Office Chithi'],
            ['date' => '2026-01-12', 'title' => 'Miteshbhai Advance', 'amount' => 200000, 'category' => 'miteshbhai_in', 'payment_method' => 'bank_transfer', 'paid_to_received_from' => 'Miteshbhai'],
            ['date' => '2026-01-15', 'title' => 'Other Sale', 'amount' => 45000, 'category' => 'other_income', 'payment_method' => 'upi', 'paid_to_received_from' => 'Walk-in Customer'],
            ['date' => '2026-01-18', 'title' => 'Vijaybhai Balance', 'amount' => 150000, 'category' => 'vijaybhai_in', 'payment_method' => 'cash', 'paid_to_received_from' => 'Vijaybhai'],
        ];

        // Sample Expense transactions
        $expenses = [
            ['date' => '2026-01-03', 'title' => 'India Post Courier', 'amount' => 2500, 'category' => 'india_post', 'payment_method' => 'cash', 'paid_to_received_from' => 'India Post Office'],
            ['date' => '2026-01-04', 'title' => 'Miteshbhai Payment', 'amount' => 180000, 'category' => 'miteshbhai_out', 'payment_method' => 'bank_transfer', 'paid_to_received_from' => 'Miteshbhai'],
            ['date' => '2026-01-06', 'title' => 'Light Bill December', 'amount' => 4500, 'category' => 'light_bill', 'payment_method' => 'upi', 'paid_to_received_from' => 'DGVCL'],
            ['date' => '2026-01-07', 'title' => 'Pani Bill', 'amount' => 800, 'category' => 'pani_bill', 'payment_method' => 'cash', 'paid_to_received_from' => 'Municipality'],
            ['date' => '2026-01-09', 'title' => 'Petrol for Delivery', 'amount' => 3000, 'category' => 'petrol', 'payment_method' => 'cash', 'paid_to_received_from' => 'Petrol Pump'],
            ['date' => '2026-01-10', 'title' => 'Shanti Jewellers Order', 'amount' => 85000, 'category' => 'shanti_jewellers', 'payment_method' => 'bank_transfer', 'paid_to_received_from' => 'Shanti Jewellers'],
            ['date' => '2026-01-11', 'title' => 'Office Safai', 'amount' => 1500, 'category' => 'safai', 'payment_method' => 'cash', 'paid_to_received_from' => 'Safai Wala'],
            ['date' => '2026-01-13', 'title' => 'Tedras Work', 'amount' => 12000, 'category' => 'tedras', 'payment_method' => 'upi', 'paid_to_received_from' => 'Tedras Shop'],
            ['date' => '2026-01-14', 'title' => 'Office Stationary', 'amount' => 2200, 'category' => 'stationary', 'payment_method' => 'cash', 'paid_to_received_from' => 'Stationery Shop'],
            ['date' => '2026-01-16', 'title' => 'Chithi Ex', 'amount' => 50000, 'category' => 'chithi_ex', 'payment_method' => 'cash', 'paid_to_received_from' => 'Chithi Account'],
            ['date' => '2026-01-17', 'title' => 'Orenge Charge', 'amount' => 5500, 'category' => 'orenge', 'payment_method' => 'upi', 'paid_to_received_from' => 'Orenge'],
            ['date' => '2026-01-19', 'title' => 'Weight Diamond', 'amount' => 25000, 'category' => 'weight_diamond', 'payment_method' => 'cash', 'paid_to_received_from' => 'Diamond Vendor'],
            ['date' => '2026-01-20', 'title' => 'Heppy Ship Courier', 'amount' => 3500, 'category' => 'heppy_ship', 'payment_method' => 'upi', 'paid_to_received_from' => 'Heppy Ship'],
            ['date' => '2026-01-21', 'title' => 'Angadia Parcel', 'amount' => 8000, 'category' => 'angadia', 'payment_method' => 'cash', 'paid_to_received_from' => 'Angadia Service'],
            ['date' => '2026-01-22', 'title' => 'Miscellaneous Expense', 'amount' => 1800, 'category' => 'other_expense', 'payment_method' => 'cash', 'paid_to_received_from' => 'Various'],
        ];

        // Insert income transactions
        foreach ($incomes as $income) {
            Expense::create([
                'date' => $income['date'],
                'title' => $income['title'],
                'amount' => $income['amount'],
                'transaction_type' => 'in',
                'category' => $income['category'],
                'payment_method' => $income['payment_method'],
                'paid_to_received_from' => $income['paid_to_received_from'],
                'admin_id' => $adminId,
            ]);
        }

        // Insert expense transactions
        foreach ($expenses as $expense) {
            Expense::create([
                'date' => $expense['date'],
                'title' => $expense['title'],
                'amount' => $expense['amount'],
                'transaction_type' => 'out',
                'category' => $expense['category'],
                'payment_method' => $expense['payment_method'],
                'paid_to_received_from' => $expense['paid_to_received_from'],
                'admin_id' => $adminId,
            ]);
        }

        $this->command->info('✅ Created ' . count($incomes) . ' income and ' . count($expenses) . ' expense transactions.');
    }
}
