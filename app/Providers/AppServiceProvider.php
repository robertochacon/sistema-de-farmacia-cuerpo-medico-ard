<?php

namespace App\Providers;

use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider;
use App\Models\MedicationEntry;
use App\Observers\MedicationEntryObserver;
use App\Models\MedicationOutput;
use App\Observers\MedicationOutputObserver;
use App\Models\Medication;
use App\Observers\MedicationObserver;
use App\Models\MedicationOutputItem;
use App\Observers\MedicationOutputItemObserver;
use App\Models\MedicationEntryItem;
use App\Observers\MedicationEntryItemObserver;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale('es');

        MedicationEntry::observe(MedicationEntryObserver::class);
        MedicationOutput::observe(MedicationOutputObserver::class);
        Medication::observe(MedicationObserver::class);
        MedicationOutputItem::observe(MedicationOutputItemObserver::class);
        MedicationEntryItem::observe(MedicationEntryItemObserver::class);
    }
}
