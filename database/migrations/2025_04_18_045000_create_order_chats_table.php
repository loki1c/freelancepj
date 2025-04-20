<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('order_chats'); // удалим старую таблицу, если она была

        Schema::create('order_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['order_id', 'user1_id', 'user2_id'], 'unique_chat_per_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_chats');
    }
};


