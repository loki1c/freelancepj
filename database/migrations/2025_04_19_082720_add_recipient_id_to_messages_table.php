<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientIdToMessagesTable extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->nullable();  // Добавляем столбец для получателя
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade'); // Создаем внешний ключ
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['recipient_id']); // Убираем внешний ключ
            $table->dropColumn('recipient_id');  // Убираем столбец
        });
    }
}
