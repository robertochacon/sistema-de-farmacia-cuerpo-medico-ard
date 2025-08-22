<?php

namespace App\Filament\Resources\MedicationEntryResource\Pages;

use App\Filament\Resources\MedicationEntryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListMedicationEntries extends ListRecords
{
    protected static string $resource = MedicationEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('pdf_report')
                ->label('PDF')
                ->icon('heroicon-o-document')
                ->url(function () {
                    $filters = request()->input('table.filters', []);
                    $params = [];
                    if (!empty($filters['date']['from'] ?? null)) $params['from'] = $filters['date']['from'];
                    if (!empty($filters['date']['until'] ?? null)) $params['until'] = $filters['date']['until'];
                    if (!empty($filters['entry_type'] ?? null)) $params['entry_type'] = $filters['entry_type'];
                    if (!empty($filters['organization']['organization_id'] ?? null)) $params['organization_id'] = $filters['organization']['organization_id'];
                    return route('reports.entries.pdf', $params);
                })
                ->openUrlInNewTab()
                ->color('danger'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        // Only show current user's records
        return $query->where('user_id', Auth::id());
    }
} 