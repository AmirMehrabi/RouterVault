<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diff_alert_settings', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('ignored_keywords');
            $table->string('telegram_bot_token')->nullable()->after('telegram_chat_id');
            $table->json('email_recipients')->nullable()->after('telegram_bot_token');
        });
    }

    public function down(): void
    {
        Schema::table('diff_alert_settings', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_bot_token', 'email_recipients']);
        });
    }
};
