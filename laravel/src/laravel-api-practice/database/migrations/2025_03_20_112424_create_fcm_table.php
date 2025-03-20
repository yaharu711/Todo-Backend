<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fcm', function (Blueprint $table) {
            // user_idカラムを追加
            $table->unsignedBigInteger('user_id');
            $table->text('token');
            $table->timestamp('created_at')->now();

            // 全てが無効になるわけではなく、デバイスごとにもトークンは発行されるため、user_idとtokenがpkとなる
            $table->primary(['user_id', 'token']);
            $table->foreign('user_id')->references('id')->on('users')->OnDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcm', function (Blueprint $table) {
            //
        });
    }
};
