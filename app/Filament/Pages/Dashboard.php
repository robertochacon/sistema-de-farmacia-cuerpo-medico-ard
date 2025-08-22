<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        $user = Auth::guard('web')->user();
        if ($user && method_exists($user, 'isEmployee') && $user->isEmployee()) {
            $this->redirectRoute('filament.admin.resources.medication-outputs.create');
        }
    }
}
