<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicationEntry;

class MedicationEntryPolicy
{
    public function update(User $user, MedicationEntry $entry): bool
    {
        return (int) $entry->user_id === (int) $user->id;
    }

    public function delete(User $user, MedicationEntry $entry): bool
    {
        return (int) $entry->user_id === (int) $user->id;
    }
}
