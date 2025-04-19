<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_chats', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }

    public function down(): void
    {
        Schema::table('order_chats', function (Blueprint $table) {
            $table->text('message')->nullable(); // можно nullable, чтобы избежать проблем при откате
        });
    }
};

