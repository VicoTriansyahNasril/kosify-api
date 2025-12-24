<?php

namespace App\Policies;

use App\Models\BoardingHouse;
use App\Models\User;

class BoardingHousePolicy
{
    public function view(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->id === $boardingHouse->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->id === $boardingHouse->user_id;
    }

    public function delete(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->id === $boardingHouse->user_id;
    }
}