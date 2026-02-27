<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\UserInviteNotification;

class SendUserInviteMail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
        use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        $user->notify(new UserInviteNotification($user));
    }
}
