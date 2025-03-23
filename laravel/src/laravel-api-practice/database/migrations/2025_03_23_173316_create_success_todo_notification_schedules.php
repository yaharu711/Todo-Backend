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
        Schema::create('failed_todo_notification_schedules', function (Blueprint $table) {
            // 通知はTodoにつき一つまでだが、失敗する回数は何回かわからないので（いつかリトライとかする可能性もあるため）、主キーはidで設定
            $table->id();
            $table->unsignedBigInteger('todo_id');
            $table->timestamp('notificate_at');
            $table->text('failed_reason');
            $table->timestamp('created_at');

            $table->foreign('todo_id')
                  ->references('id')->on('todos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('success_todo_notification_schedules', function (Blueprint $table) {
            //
        });
    }
};
