<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Запуск миграции.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // Идентификатор сообщения
            $table->foreignId('order_chat_id')->constrained('order_chats')->onDelete('cascade'); // Связь с заказом
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Связь с пользователем
            $table->text('content'); // Содержимое сообщения
            $table->timestamps(); // Время создания и обновления
        });
    }

    /**
     * Откат миграции.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
