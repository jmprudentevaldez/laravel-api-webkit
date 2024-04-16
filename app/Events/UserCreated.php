<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $user;

    public string $temporaryPassword;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, string $temporaryPassword)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
    }
}
