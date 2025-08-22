<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicationOutput;

class MedicationOutputPolicy
{
    public function update(User $user, MedicationOutput $output): bool
    {
        return (int) $output->user_id === (int) $user->id;
    }

    public function delete(User $user, MedicationOutput $output): bool
    {
        return (int) $output->user_id === (int) $user->id;
    }
}
