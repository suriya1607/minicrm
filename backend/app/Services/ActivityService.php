<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityService
{
    public function log(
        object $subject,
        string $type,
        string $title,
        string $description = ''
    ): Activity {
        return Activity::create([
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->id,
            'user_id'      => Auth::id(),
            'type'         => $type,
            'title'        => $title,
            'description'  => $description,
        ]);
    }
}