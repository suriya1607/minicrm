<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'assigned_to',
        'title',
        'source',
        'stage',
        'notes',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function deal()
    {
        return $this->hasOne(Deal::class);
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