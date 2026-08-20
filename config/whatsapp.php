<?php

return [
    'url' => env('WHATSAPP_BRIDGE_URL', 'http://localhost:3000'),
    'api_key' => env('WHATSAPP_API_KEY'),
    'session' => env('WHATSAPP_SESSION', 'main'),
    'timeout' => (int) env('WHATSAPP_TIMEOUT', 120),
    'country_code' => env('WHATSAPP_COUNTRY_CODE', '20'),
    'admin_phone' => env('WHATSAPP_ADMIN_PHONE'),
    'reminder_minutes' => (int) env('WHATSAPP_REMINDER_MINUTES', 60),
];
