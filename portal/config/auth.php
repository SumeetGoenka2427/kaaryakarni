<?php

use App\Models\AdminUser;
use App\Models\Member;

/*
| Two completely separate login systems:
|   admin  → admin panel, username + password (admin_users)
|   member → website member area, mobile OTP (members)
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
        'member' => [
            'driver' => 'session',
            'provider' => 'members',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => AdminUser::class,
        ],
        'members' => [
            'driver' => 'eloquent',
            'model' => Member::class,
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
