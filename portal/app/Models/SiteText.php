<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Admin override of a website UI text key (defaults live in lang/{en,hi}/*.php). */
class SiteText extends Model
{
    protected $fillable = ['group', 'key', 'value_en', 'value_hi'];
}
