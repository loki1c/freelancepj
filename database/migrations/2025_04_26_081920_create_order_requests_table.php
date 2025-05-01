<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['Ожидает подтверждения', 'Принят', 'Отклонен'])->default('Ожидает подтверждения');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_requests');
    }
}


