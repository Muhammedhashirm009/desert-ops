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
        Schema::create('sales_log_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_log_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_sold', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_revenue', 12, 2);
            $table->decimal('commission_amount', 12, 2)->default(0.00);
            $table->decimal('net_revenue', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_log_items');
    }
};
