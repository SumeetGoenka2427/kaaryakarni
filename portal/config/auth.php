<?php

use App\Models\AdminUser;

/*
| Two completely separate login systems:
|   admin  → admin panel, username + password (admin_users)
|   member → website member area, mobile OTP (members) — added in P04
*/

return [

    'defaults' => [
        'guard' => 'admin',
        'passwords' => 'admins',
    ],

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => AdminUser::class,
        ],
    ],

    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 30,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
