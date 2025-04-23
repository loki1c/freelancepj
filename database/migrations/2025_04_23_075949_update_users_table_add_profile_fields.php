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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('login')->nullable()->after('id'); // без unique
            $table->string('firstname')->after('login');
            $table->string('lastname')->after('firstname');
            $table->string('phone')->nullable()->after('password');
            $table->string('city')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login', 'firstname', 'lastname', 'phone', 'city']);
            $table->string('name')->after('id');
        });
    }
};

