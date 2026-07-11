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
        Schema::create('outlet_production_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('run_number')->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity_produced', 12, 2);
            $table->date('prepared_date');
            $table->string('status')->default('pending'); // pending, completed
            $table->string('destination')->default('kitchen'); // kitchen, store, showcase
            $table->string('prepared_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('outlet_production_run_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_production_run_id')->constrained('outlet_production_runs')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->decimal('quantity_used', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_production_run_materials');
        Schema::dropIfExists('outlet_production_runs');
    }
};
