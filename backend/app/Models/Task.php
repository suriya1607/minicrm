<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'taskable_type', 'taskable_id',
        'assigned_to', 'title', 'description',
        'priority', 'status', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function taskable()
    {
        return $this->morphTo();
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}