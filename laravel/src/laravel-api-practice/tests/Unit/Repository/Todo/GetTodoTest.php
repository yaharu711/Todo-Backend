<?php

namespace Tests\Unit\Repository\Todo;

use App\Models\Todo;
use App\Models\TodoModel;
use App\Models\User;
use App\Repositories\TodoRepository;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetTodoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function getTodo_should_return_TodoModel_when_record_exists(): void
    {
        // 1. Setup
        $now = new DateTimeImmutable('2023-01-01 12:34:56');
        $user = User::factory()->create();
        $todo = Todo::factory()->for($user)->create([
            'name' => 'Test Title',
            'memo' => 'Test Memo',
            'is_completed' => false,
            'imcompleted_at' => $now,
            'created_at' => $now,
        ]);
        $notificationTime = '2023-01-10 09:00:00';
        DB::table('todo_notification_schedules')->insert([
            'todo_id' => $todo->id,
            'notificate_at' => $notificationTime,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $repository = new TodoRepository($now);

        // 2. Action
        $result = $repository->getTodo($user->id, $todo->id);

        // 3. Assertions
        $this->assertInstanceOf(TodoModel::class, $result);
        $this->assertEquals($todo->id, $result->id);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals($todo->name, $result->name);
        $this->assertEquals($todo->memo, $result->memo);
        $this->assertEquals(new DateTimeImmutable($notificationTime), $result->notificate_at);
        $this->assertFalse($result->is_completed);
    }
}
