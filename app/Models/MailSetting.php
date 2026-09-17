<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'user_id', 
        'subject', 
        'body', 
        'attachment_path', 
        'search_keywords', 
        'is_auto_hunting',
        'google_sheet_id',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'from_address',
        'from_name'
    ];

    protected function casts(): array
    {
        return [
            'is_auto_hunting' => 'boolean',
            'smtp_password' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
