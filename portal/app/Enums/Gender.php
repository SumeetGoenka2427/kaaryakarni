<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Member / family member gender.
 */
enum Gender: string
{
    use HasLabel;

    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
}
