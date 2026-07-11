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
        Schema::create('outlet_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('salesperson'); // outlet_admin, salesperson
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Add employee tracking to sales_logs
        Schema::table('sales_logs', function (Blueprint $table) {
            $table->foreignId('logged_by_employee_id')->nullable()->after('outlet_id')
                  ->constrained('outlet_employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_logs', function (Blueprint $table) {
            $table->dropForeign(['logged_by_employee_id']);
            $table->dropColumn('logged_by_employee_id');
        });

        Schema::dropIfExists('outlet_employees');
    }
};
