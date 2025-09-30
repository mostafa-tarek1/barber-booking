<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "اختبار relationship بين Booking والـ Employee...\n";

try {
  $booking = \App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking::with(['services', 'employee'])->first();

  if ($booking) {
    echo "الحجز ID: {$booking->id}\n";
    echo "اسم العميل: {$booking->name}\n";

    if ($booking->employee) {
      echo "الموظف ID: {$booking->employee->id}\n";
      echo "اسم الموظف: {$booking->employee->first_name} {$booking->employee->last_name}\n";
    } else {
      echo "لا يوجد موظف مرتبط\n";
    }

    if ($booking->services) {
      echo "الخدمات: " . $booking->services->count() . " خدمة\n";
    }

  } else {
    echo "لا توجد حجوزات في قاعدة البيانات\n";
  }

} catch (Exception $e) {
  echo "خطأ: " . $e->getMessage() . "\n";
  echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
