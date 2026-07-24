<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE employees MODIFY employee_type ENUM('admin', 'super_admin', 'employee', 'barber') NOT NULL DEFAULT 'admin'"
            );
        }
    }

    public function down(): void
    {
        // Existing barber records make narrowing the enum destructive, so rollback intentionally preserves it.
    }
};
