<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskOrder extends Model
{
    protected $table = 'task_orders';

    protected $fillable = [
        'user_id',
        'task_id',
        'sort_order',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
