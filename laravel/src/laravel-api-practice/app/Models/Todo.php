<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;
    // テーブル名を指定する
    protected $table = 'todos';

    protected $fillable = [
        'name',
        'is_completed',
        'created_at',
        'completed_at',
        'imcompleted_at',
    ];
}
