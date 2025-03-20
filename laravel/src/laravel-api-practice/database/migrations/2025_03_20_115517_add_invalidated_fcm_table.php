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
        Schema::create('invalidated_fcm', function (Blueprint $table) {
            // user_idカラムを追加
            $table->unsignedBigInteger('user_id');
            $table->text('token');
            $table->timestamp('created_at')->now();
            
            $table->primary(['user_id', 'token']);
            $table->foreign('user_id')->references('id')->on('users')->OnDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invalidated_fcm', function (Blueprint $table) {
            //
        });
    }
};
