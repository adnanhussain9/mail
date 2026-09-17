<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('google_sheet_id')->nullable()->after('user_id');
            $table->string('smtp_host')->nullable();
            $table->string('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'google_sheet_id', 'smtp_host', 'smtp_port', 
                'smtp_username', 'smtp_password', 'smtp_encryption', 
                'from_address', 'from_name'
            ]);
        });
    }
};
