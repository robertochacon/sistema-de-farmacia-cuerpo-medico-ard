<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationEntryResource\Pages;
use App\Models\MedicationEntry;
use App\Models\Medication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicationEntryResource extends Resource
{
    protected static ?string $model = MedicationEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';
    
    protected static ?string $modelLabel = 'Entrada de medicamentos';

    protected static ?string $pluralModelLabel = 'Entradas de medicamentos';

    protected static ?string $navigationLabel = 'Entradas';

    protected static ?string $navigationGroup = 'Farmacia';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('entry_type')
                    ->searchable()
                    ->label('Tipo de entrada')
                    ->options([
                        'donation' => 'Donación',
                        'order' => 'Pedido',
                        'purchase' => 'Compra',
                    ])->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('items')
                    ->label('Medicamentos')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('medication_id')
                            ->label('Medicamento')
                            ->options(fn () => Medication::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()->minValue(1)
                            ->required()
                            ->label('Cantidad'),
                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->default(0)
                            ->label('Precio unitario'),
                        Forms\Components\DatePicker::make('expiration_date')
                            ->label('Vence'),
                        Forms\Components\TextInput::make('lot_number')
                            ->label('Lote'),
                    ])
                    ->columns(5)
                    ->createItemButtonLabel('Agregar medicamento')
                    ->collapsed(false)
                    ->grid(1)
                    ->minItems(1)
                    ->reorderable(false),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\BadgeColumn::make('entry_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'donation',
                        'warning' => 'order',
                        'primary' => 'purchase',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'donation' => 'Donación',
                        'order' => 'Pedido',
                        'purchase' => 'Compra',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Ítems'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Fecha'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMedicationEntries::route('/'),
            'create' => Pages\CreateMedicationEntry::route('/create'),
            'edit' => Pages\EditMedicationEntry::route('/{record}/edit'),
        ];
    }
} 