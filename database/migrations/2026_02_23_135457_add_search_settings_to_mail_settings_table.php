<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->string('search_keywords')->nullable();
            $table->boolean('is_auto_hunting')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            //
        });
    }
};
