<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** Marital status of a family member. */
enum MaritalStatus: string
{
    use HasLabel;

    case Unmarried = 'unmarried';
    case Married = 'married';
    case Widowed = 'widowed';
    case Divorced = 'divorced';
}
