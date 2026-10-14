<?php

/*
|--------------------------------------------------------------------------
| Khandelwal Samaj Portal configuration
|--------------------------------------------------------------------------
| Infrastructure settings that come from the environment. Business settings
| the admin can change (membership fee, contact info, toggles) live in the
| `settings` table, not here.
*/

return [

    // Website languages. The first one is the default and has no URL prefix.
    'locales' => [
        'en' => ['name' => 'English', 'native' => 'English'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी'],
    ],

    'admin' => [
        'idle_timeout' => (int) env('ADMIN_IDLE_TIMEOUT', 30),
        'seed' => [
            'name' => env('ADMIN_SEED_NAME', 'Samaj Admin'),
            'username' => env('ADMIN_SEED_USERNAME', 'admin'),
            'email' => env('ADMIN_SEED_EMAIL'),
            'password' => env('ADMIN_SEED_PASSWORD'),
        ],
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'msg91' => [
            'auth_key' => env('MSG91_AUTH_KEY'),
            'sender_id' => env('MSG91_SENDER_ID'),
            'otp_template_id' => env('MSG91_OTP_TEMPLATE_ID'),
        ],
    ],

    'otp' => [
        'length' => (int) env('OTP_LENGTH', 6),
        'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 5),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
        'max_per_mobile_per_hour' => (int) env('OTP_MAX_SENDS_PER_MOBILE_PER_HOUR', 5),
        'max_per_ip_per_hour' => (int) env('OTP_MAX_SENDS_PER_IP_PER_HOUR', 20),
    ],

    'payments' => [
        'driver' => env('PAYMENT_DRIVER', 'razorpay'),
        'currency' => 'INR',
        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID'),
            'key_secret' => env('RAZORPAY_KEY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        ],
    ],

    'uploads' => [
        'image_max_kb' => 5120,
        'pdf_max_kb' => 5120,
        'logo_max_kb' => 2048,
        'image_widths' => [480, 960, 1600],
    ],
];
