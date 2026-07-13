<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('retail_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('retail_price');
        });

        Schema::table('outlet_catalog_items', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('retail_price');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
        Schema::table('outlet_catalog_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
