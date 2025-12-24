<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        return $user->id === $room->boardingHouse->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Room $room): bool
    {
        return $user->id === $room->boardingHouse->user_id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->id === $room->boardingHouse->user_id;
    }
}