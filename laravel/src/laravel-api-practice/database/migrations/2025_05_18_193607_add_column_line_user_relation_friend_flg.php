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
        Schema::table('line_user_relation', function (Blueprint $table) {
            $table->boolean('friend_flag')
                ->default(false)
                ->comment('LINE公式アカウントを友だちなら true');
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
