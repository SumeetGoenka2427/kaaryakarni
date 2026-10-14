<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** SMS delivery record. Never contains the message text. */
class SmsLog extends Model
{
    protected $fillable = ['mobile', 'template_key', 'provider', 'status', 'provider_message_id', 'error'];
}
