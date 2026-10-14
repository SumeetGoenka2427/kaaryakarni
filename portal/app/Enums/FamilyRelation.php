<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * A family member's relation to the member.
 */
enum FamilyRelation: string
{
    use HasLabel;

    case Spouse = 'spouse';
    case Son = 'son';
    case Daughter = 'daughter';
    case Father = 'father';
    case Mother = 'mother';
    case Brother = 'brother';
    case Sister = 'sister';
    case DaughterInLaw = 'daughter_in_law';
    case SonInLaw = 'son_in_law';
    case Grandson = 'grandson';
    case Granddaughter = 'granddaughter';
    case Other = 'other';
}
