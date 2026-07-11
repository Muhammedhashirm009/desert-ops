<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet_production_runs', function (Blueprint $table) {
            $table->foreignId('outlet_catalog_item_id')->nullable()->constrained('outlet_catalog_items')->nullOnDelete();
        });

        Schema::table('outlet_stocks', function (Blueprint $table) {
            $table->foreignId('outlet_catalog_item_id')->nullable()->constrained('outlet_catalog_items')->nullOnDelete();
        });

        Schema::table('outlet_stock_movements', function (Blueprint $table) {
            $table->foreignId('outlet_catalog_item_id')->nullable()->constrained('outlet_catalog_items')->nullOnDelete();
        });

        Schema::table('sales_log_items', function (Blueprint $table) {
            $table->foreignId('outlet_catalog_item_id')->nullable()->constrained('outlet_catalog_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('outlet_production_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_catalog_item_id');
        });

        Schema::table('outlet_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_catalog_item_id');
        });

        Schema::table('outlet_stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_catalog_item_id');
        });

        Schema::table('sales_log_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_catalog_item_id');
        });
    }
};
