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
        Schema::table('materials', function (Blueprint $table) {
            $table->integer('per_box_qty')->nullable();
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('material_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('outlet_stocks', function (Blueprint $table) {
            $table->dropUnique(['outlet_id', 'product_id']);
            
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('material_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->unique(['outlet_id', 'product_id']);
            $table->unique(['outlet_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlet_stocks', function (Blueprint $table) {
            $table->dropUnique(['outlet_id', 'material_id']);
            $table->dropUnique(['outlet_id', 'product_id']);
            $table->dropColumn('material_id');
            $table->unique(['outlet_id', 'product_id']);
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->dropColumn('material_id');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('per_box_qty');
        });
    }
};
