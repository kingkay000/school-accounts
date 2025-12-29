<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\BankLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chart of Accounts
        $accounts = [
            ['code' => '5300', 'name' => 'Fuel/Diesel', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Stationery', 'type' => 'expense'],
            ['code' => '4400', 'name' => 'Food/Groceries', 'type' => 'expense'],
            ['code' => '1001', 'name' => 'Bank Account', 'type' => 'asset'],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::firstOrCreate(['code' => $acc['code']], $acc);
        }

        // Bank Logs
        BankLog::create([
            'transaction_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'description' => 'DIESEL SUPPLY ABC LTD',
            'amount' => 50000.00,
            'type' => 'debit',
            'status' => 'unverified'
        ]);

        BankLog::create([
            'transaction_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'description' => 'OFFICE STATIONERY',
            'amount' => 12500.00,
            'type' => 'debit',
            'status' => 'unverified'
        ]);

        BankLog::create([
            'transaction_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'description' => 'OLD TRANSACTION',
            'amount' => 5000.00,
            'type' => 'debit',
            'status' => 'verified'
        ]);
    }
}
