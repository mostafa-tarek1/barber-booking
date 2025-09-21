<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, we need to drop the existing enum constraint
        DB::statement("ALTER TABLE employees MODIFY COLUMN employee_type ENUM('admin', 'super_admin', 'employee', 'barber') NOT NULL DEFAULT 'employee'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum values
        DB::statement("UPDATE employees SET employee_type = 'employee' WHERE employee_type = 'barber'");
        DB::statement("ALTER TABLE employees MODIFY COLUMN employee_type ENUM('admin', 'super_admin', 'employee') NOT NULL DEFAULT 'employee'");
    }
};
