<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['outlet_id', 'product_id']);
            $table->unique(['outlet_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_product');
    }
};
