<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDataSeeder extends Seeder
{
    /**
     * Run the database seeds to reset transaction data and stocks.
     */
    public function run(): void
    {
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();

        // 1. Truncate transaction/log tables
        $tablesToTruncate = [
            'purchase_order_items',
            'purchase_orders',
            'goods_received_note_items',
            'goods_received_notes',
            'dispatch_items',
            'dispatches',
            'production_run_materials',
            'production_runs',
            'outlet_production_run_materials',
            'outlet_production_runs',
            'sales_log_items',
            'sales_logs',
            'material_request_items',
            'material_requests',
            'showcase_requests',
            'showcase_request_items',
            'price_histories',
            'supplier_bills',
            'franchise_invoices',
            'journal_entries',
            'journal_transactions',
            'outlet_stock_movements',
        ];

        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("Truncated table: {$table}");
            }
        }

        // Enable foreign key constraints
        Schema::enableForeignKeyConstraints();

        // 2. Reset raw materials stock and WAC to 0
        if (Schema::hasTable('materials')) {
            DB::table('materials')->update([
                'current_stock' => 0.00,
                'kitchen_stock' => 0.00,
                'cost_price' => 0.00,
            ]);
            $this->command->info("Reset all materials stock and cost_price to 0.");
        }

        // 3. Reset product stock and cost to 0
        if (Schema::hasTable('products')) {
            DB::table('products')->update([
                'current_kitchen_stock' => 0.00,
                'cost_price' => 0.00,
            ]);
            $this->command->info("Reset all products kitchen stock and cost_price to 0.");
        }

        // 4. Reset outlet stocks to 0
        if (Schema::hasTable('outlet_stocks')) {
            DB::table('outlet_stocks')->update([
                'quantity' => 0.00,
                'store_quantity' => 0.00,
                'kitchen_quantity' => 0.00,
                'showcase_quantity' => 0.00,
            ]);
            $this->command->info("Reset all outlet_stocks quantities to 0.");
        }

        $this->command->info("Database reset completed successfully!");
    }
}
