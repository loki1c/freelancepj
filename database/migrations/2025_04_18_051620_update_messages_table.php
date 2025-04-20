<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMessagesTable extends Migration
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
            $table->foreignId('order_chat_id')->constrained('order_chats')->onDelete('cascade'); // Связь с чатом
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Связь с пользователем
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('set null'); // Связь с получателем
            $table->text('content'); // Содержимое сообщения
            $table->boolean('is_read')->default(false); // Статус прочтения (по желанию)
            $table->timestamps(); // Время создания и обновления

            // Добавление индексов для улучшения производительности
            $table->index('order_chat_id');
            $table->index('sender_id');
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

