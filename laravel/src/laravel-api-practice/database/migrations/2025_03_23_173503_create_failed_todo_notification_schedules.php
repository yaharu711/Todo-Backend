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
        Schema::create('success_todo_notification_schedules', function (Blueprint $table) {
            // 各Todoにつき一件のみの通知スケジュールを設定できるようにするため、todo_idを主キーに設定
            // と思ったが、一つのTodoで何回か通知を設定して受信することはあり得るので、主キーにはしない
            $table->id();
            $table->unsignedBigInteger('todo_id');
            // まだ小規模なアプリケーションのため、indexは設定しない
            $table->timestamp('notificate_at');
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
        Schema::table('failed_todo_notification_schedules', function (Blueprint $table) {
            //
        });
    }
};
