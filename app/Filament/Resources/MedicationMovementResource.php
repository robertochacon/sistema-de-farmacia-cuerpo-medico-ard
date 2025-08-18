<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationMovementResource\Pages;
use App\Models\MedicationMovement;
use App\Models\Medication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationMovementResource extends Resource
{
    protected static ?string $model = MedicationMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $modelLabel = 'Movimiento de medicamentos';

    protected static ?string $pluralModelLabel = 'Movimientos de medicamentos';

    protected static ?string $navigationLabel = 'Movimientos de medicamentos';

    protected static ?string $navigationGroup = 'Farmacia';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Fecha')->sortable(),
                Tables\Columns\TextColumn::make('medication.name')->label('Medicamento')->searchable(),
                Tables\Columns\BadgeColumn::make('type')->label('Tipo')->colors([
                    'success' => 'in',
                    'danger' => 'out',
                ])->formatStateUsing(function ($state) {
                    return $state === 'in' ? 'Entrada' : 'Salida';
                }),
                Tables\Columns\TextColumn::make('quantity')->label('Cantidad'),
                Tables\Columns\TextColumn::make('balance')->label('Saldo'),
                Tables\Columns\TextColumn::make('notes')->label('Notas')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('medication_id')
                    ->label('Medicamento')
                    ->options(fn () => Medication::orderBy('name')->pluck('name', 'id')->all()),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['in' => 'Entrada', 'out' => 'Salida']),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\MedicationMovementExporter::class)
                    ->label('Exportar')
                    ->fileName(fn () => 'movimientos_'.now()->format('Ymd_His')),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicationMovements::route('/'),
        ];
    }
} 