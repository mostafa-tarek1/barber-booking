<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 12, 2);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('package_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unique(['package_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('package_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unique(['package_id', 'service_id']);
            $table->timestamps();
        });

        Schema::create('booking_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('name_snapshot');
            $table->decimal('price_snapshot', 12, 2);
            $table->unsignedInteger('duration_snapshot');
            $table->timestamps();
        });

        Schema::table('booking_service', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('service_id')->constrained('packages')->nullOnDelete();
            $table->string('name_snapshot')->nullable()->after('employee_id');
            $table->decimal('price_snapshot', 12, 2)->nullable()->after('name_snapshot');
            $table->unsignedInteger('duration_snapshot')->nullable()->after('price_snapshot');
            $table->unsignedSmallInteger('quantity')->default(1)->after('duration_snapshot');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status')->index();
            $table->softDeletes();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('service_id')->constrained('packages')->nullOnDelete();
        });

        Schema::create('barber_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('from_hour')->nullable();
            $table->time('to_hour')->nullable();
            $table->boolean('is_day_off')->default(false);
            $table->timestamps();
            $table->unique(['employee_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_working_hours');

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });

        Schema::table('booking_service', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
            $table->dropColumn(['name_snapshot', 'price_snapshot', 'duration_snapshot', 'quantity']);
        });

        Schema::dropIfExists('booking_package');
        Schema::dropIfExists('package_service');
        Schema::dropIfExists('package_translations');
        Schema::dropIfExists('packages');
    }
};
