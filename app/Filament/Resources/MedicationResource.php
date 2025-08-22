<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationResource\Pages;
use App\Models\Medication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MedicationResource extends Resource
{
    protected static ?string $model = Medication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Medicamento';

    protected static ?string $pluralModelLabel = 'Medicamentos';

    protected static ?string $navigationLabel = 'Medicamentos';

    protected static ?string $navigationGroup = 'Farmacia';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                Forms\Components\TextInput::make('generic_name')->label('Genérico'),
                Forms\Components\Select::make('presentation')
                    ->label('Presentación')
                    ->options(fn () => Medication::query()
                        ->whereNotNull('presentation')
                        ->where('presentation', '!=', '')
                        ->distinct()
                        ->orderBy('presentation')
                        ->pluck('presentation', 'presentation'))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('presentation')
                            ->label('Presentación nueva')
                            ->required(),
                    ])
                    ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action
                        ->modalHeading('Agregar presentación')
                        ->modalSubmitActionLabel('Agregar')
                        ->modalWidth('sm'))
                    ->createOptionUsing(function (array $data) {
                        $value = strtoupper(trim((string) ($data['presentation'] ?? '')));
                        return $value !== '' ? $value : null;
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('concentration')->label('Concentración'),
                Forms\Components\TextInput::make('manufacturer')->label('Fabricante'),
                Forms\Components\TextInput::make('lot_number')
                    ->label('Lote')
                    ->disabledOn('edit')
                    ->hidden(fn (?Model $record) => filled($record))
                    ->helperText('Se define mediante Entradas; no editable en Edición.'),
                Forms\Components\DatePicker::make('expiration_date')
                    ->label('Vence')
                    ->required()
                    ->disabledOn('edit')
                    ->hidden(fn (?Model $record) => filled($record))
                    ->helperText('Se actualiza con Entradas; no editable en Edición.'),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->label('Cantidad')
                    ->disabledOn('edit')
                    ->hidden(fn (?Model $record) => filled($record))
                    ->helperText('Los ajustes de inventario deben realizarse desde Entradas/Salidas.'),
                Forms\Components\TextInput::make('unit_price')->numeric()->label('Precio unitario'),
                Forms\Components\Select::make('entry_type')
                    ->options([
                        'donation' => 'Donación',
                        'order' => 'Pedido',
                        'purchase' => 'Compra',
                    ])
                    ->label('Tipo de entrada')
                    ->required()
                    ->disabledOn('edit')
                    ->hidden(fn (?Model $record) => filled($record))
                    ->helperText('Solo se define al crear; no editable en Edición.'),
                Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
                Forms\Components\Toggle::make('status')->label('Activo')->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('presentation')->label('Presentación'),
                Tables\Columns\TextColumn::make('concentration')->label('Concentración')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity')->label('Existencia')->sortable(),
                Tables\Columns\TextColumn::make('unit_price')->label('Precio')->money('DOP')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expiration_date')->date()->label('Vence')->date('d/m/Y')->sortable(),
                Tables\Columns\ToggleColumn::make('status')->label('Activo'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->beforeFormFilled(function ($record, $action) {
                        // no-op
                    })
                    ->after(function (Medication $record) {
                        // Warn if stock decreased
                        $original = (int) $record->getOriginal('quantity');
                        $new = (int) $record->quantity;
                        if ($new < $original) {
                            Notification::make()
                                ->warning()
                                ->title('Stock reducido')
                                ->body('Has reducido la cantidad del medicamento. Se registró un movimiento de salida.')
                                ->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedications::route('/'),
            'create' => Pages\CreateMedication::route('/create'),
            'edit' => Pages\EditMedication::route('/{record}/edit'),
        ];
    }
}
