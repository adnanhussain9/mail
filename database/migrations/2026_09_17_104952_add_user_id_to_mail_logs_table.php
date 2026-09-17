<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            
            // Drop the old unique index, create a new one with user_id
            $table->dropUnique('unique_mail_entry');
            $table->unique(['user_id', 'email', 'company_name', 'position_name'], 'unique_user_mail_entry');
        });
    }

    public function down(): void
    {
        Schema::table('mail_logs', function (Blueprint $table) {
            $table->dropUnique('unique_user_mail_entry');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unique(['email', 'company_name', 'position_name'], 'unique_mail_entry');
        });
    }
};
