<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'lead_id',
        'assigned_to',
        'title',
        'value',
        'stage',
        'expected_close_date',
        'notes',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'value'               => 'decimal:2',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable')->orderBy('due_date');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject')->orderBy('created_at', 'desc');
    }
}