<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet_production_runs', function (Blueprint $table) {
            $table->decimal('qty_to_store', 12, 2)->default(0)->after('destination');
            $table->decimal('qty_to_showcase', 12, 2)->default(0)->after('qty_to_store');
        });
    }

    public function down(): void
    {
        Schema::table('outlet_production_runs', function (Blueprint $table) {
            $table->dropColumn(['qty_to_store', 'qty_to_showcase']);
        });
    }
};
