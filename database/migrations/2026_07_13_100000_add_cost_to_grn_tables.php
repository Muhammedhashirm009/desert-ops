<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_received_note_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->default(0)->after('quantity_received');
            $table->decimal('line_cost', 12, 2)->default(0)->after('unit_cost');
        });

        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->decimal('total_cost', 12, 2)->default(0)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('goods_received_note_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'line_cost']);
        });
        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });
    }
};
