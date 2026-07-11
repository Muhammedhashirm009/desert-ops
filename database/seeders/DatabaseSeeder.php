<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create the 5 requested users
        User::factory()->create([
            'name' => 'Hashir Admin',
            'email' => 'admin@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Accountant User',
            'email' => 'accountant_user@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'accountant',
        ]);

        User::factory()->create([
            'name' => 'General Manager',
            'email' => 'gm@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'gm',
        ]);

        User::factory()->create([
            'name' => 'Laban Chef',
            'email' => 'laban_chef@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'laban_chef',
        ]);

        User::factory()->create([
            'name' => 'Baklava Chef',
            'email' => 'baklava_chef@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'baklava_chef',
        ]);

        User::factory()->create([
            'name' => 'Dough Chef',
            'email' => 'dough_chef@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'dough_chef',
        ]);

        User::factory()->create([
            'name' => 'Store Manager',
            'email' => 'manager@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'store_manager',
        ]);

        // 2. Seed essential Chart of Accounts (required for ledger metrics and financial balance views)
        Account::create(['code' => '1010', 'name' => 'Cash in Hand', 'type' => 'asset']);
        Account::create(['code' => '1020', 'name' => 'Bank Current Account', 'type' => 'asset']);
        Account::create(['code' => '1200', 'name' => 'Accounts Receivable (Franchises)', 'type' => 'asset']);
        Account::create(['code' => '1300', 'name' => 'Inventory - Raw Materials', 'type' => 'asset']);
        Account::create(['code' => '2100', 'name' => 'Accounts Payable (Suppliers)', 'type' => 'liability']);
        Account::create(['code' => '3000', 'name' => 'Capital Account', 'type' => 'equity']);
        Account::create(['code' => '3900', 'name' => 'Retained Earnings', 'type' => 'equity']);
        Account::create(['code' => '4010', 'name' => 'Direct Sales Revenue (Own Outlets)', 'type' => 'revenue']);
        Account::create(['code' => '4020', 'name' => 'Franchise Sales Revenue', 'type' => 'revenue']);
        Account::create(['code' => '5010', 'name' => 'Raw Material Purchases', 'type' => 'expense']);
        Account::create(['code' => '5020', 'name' => 'Production Expenses', 'type' => 'expense']);
        Account::create(['code' => '5100', 'name' => 'Rent Expense', 'type' => 'expense']);
        Account::create(['code' => '5200', 'name' => 'Utilities Expense', 'type' => 'expense']);
        Account::create(['code' => '5300', 'name' => 'Salaries & Wages', 'type' => 'expense']);
        Account::create(['code' => '5400', 'name' => 'Transport & Delivery', 'type' => 'expense']);
        Account::create(['code' => '5500', 'name' => 'Maintenance & Repairs', 'type' => 'expense']);
        Account::create(['code' => '5600', 'name' => 'Office Supplies', 'type' => 'expense']);
        Account::create(['code' => '5900', 'name' => 'General & Administrative Expenses', 'type' => 'expense']);
    }
}
