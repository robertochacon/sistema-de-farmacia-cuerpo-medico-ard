<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\MedicationEntry;
use App\Models\MedicationOutput;
use App\Policies\MedicationEntryPolicy;
use App\Policies\MedicationOutputPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        MedicationEntry::class => MedicationEntryPolicy::class,
        MedicationOutput::class => MedicationOutputPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
