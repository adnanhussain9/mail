<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'email',
        'company_name',
        'position_name',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
