<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = ['subject', 'body', 'attachment_path', 'search_keywords', 'is_auto_hunting'];
}
