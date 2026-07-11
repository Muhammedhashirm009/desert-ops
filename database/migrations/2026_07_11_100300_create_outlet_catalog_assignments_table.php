<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet_catalog_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('outlet_catalog_item_id')->constrained('outlet_catalog_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['outlet_id', 'outlet_catalog_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_catalog_assignments');
    }
};
