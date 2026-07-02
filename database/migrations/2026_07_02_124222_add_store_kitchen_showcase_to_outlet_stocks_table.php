<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('outlet_stocks', function (Blueprint $table) {
            $table->decimal('store_quantity', 10, 2)->default(0.00)->after('quantity');
            $table->decimal('kitchen_quantity', 10, 2)->default(0.00)->after('store_quantity');
            $table->decimal('showcase_quantity', 10, 2)->default(0.00)->after('kitchen_quantity');
        });

        // Copy existing quantity to store_quantity
        if (Schema::hasColumn('outlet_stocks', 'quantity')) {
            DB::table('outlet_stocks')->update([
                'store_quantity' => DB::raw('quantity')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlet_stocks', function (Blueprint $table) {
            $table->dropColumn(['store_quantity', 'kitchen_quantity', 'showcase_quantity']);
        });
    }
};
