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
        Schema::create('line_user_relation', function (Blueprint $table) {
            // 「ユーザ ↔ LINE アカウント」が完全に 1 対 1 。
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('line_user_id')->unique();
            $table->timestamp('created_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('line_user_relation', function (Blueprint $table) {
            //
        });
    }
};
