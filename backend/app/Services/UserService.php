<?php

namespace App\Services;
use App\Models\User;
use App\Events\UserCreated;

class UserService
{
    /**
     * Create a new class instance.
     */
    protected  $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUsers(array $filters, int $perPage)
    {
        $query = User::query()
            ->with('role:id,name')
            ->where('id', '!=', auth()->id());

        foreach ($filters as $field => $value) {
            if ($field === 'created_at') {
                $query->whereDate($field, $value);
            } elseif ($field === 'role_id') {
                $query->where($field, $value);
            } else {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function createUser(array $data)
    {
        $user = User::create($data);

        event(new UserCreated($user));

        return $user;
    }

    public function updateUser(User $user, array $data)
    {
        $user->update($data);

        return $user;
    }
}
