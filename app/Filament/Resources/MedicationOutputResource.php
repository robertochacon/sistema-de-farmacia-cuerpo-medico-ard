<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationOutputResource\Pages;
use App\Models\Medication;
use App\Models\MedicationOutput;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Closure;

class MedicationOutputResource extends Resource
{
    protected static ?string $model = MedicationOutput::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-left';

    protected static ?string $modelLabel = 'salida de medicamentos';

    protected static ?string $pluralModelLabel = 'Salidas de medicamentos';

    protected static ?string $navigationLabel = 'Salidas';

    protected static ?string $navigationGroup = 'Farmacia';

    protected static function computeTotals(Set $set, Get $get): void
    {
        $items = collect($get('items'));
        $sum = (int) $items->sum(fn ($i) => (int) ($i['quantity'] ?? 0));
        $set('total_quantity', $sum);

        $hasExceeding = $items->contains(function ($i) {
            $medicationId = $i['medication_id'] ?? null;
            if (! $medicationId) {
                return false;
            }
            $available = (int) (Medication::find($medicationId)?->quantity ?? 0);
            $entered = (int) ($i['quantity'] ?? 0);
            return $entered > $available;
        });
        $set('has_exceeding', $hasExceeding);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('department_id')
                    ->searchable()
                    ->label('Departamento destino')
                    ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('patient_type')
                    ->searchable()
                    ->label('Tipo de paciente')
                    ->options([
                        'military' => 'Militar',
                        'civilian' => 'Civil',
                    ])
                    ->native(false)
                    ->required(),
                Forms\Components\Repeater::make('items')
                    ->label('Medicamentos')
                    ->schema([
                        Forms\Components\Select::make('medication_id')
                            ->label('Medicamento')
                            ->options(fn () => Medication::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::computeTotals($set, $get))
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()->minValue(1)
                            ->required()
                            ->reactive()
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    $medicationId = $get('medication_id');
                                    if (! $medicationId) {
                                        return;
                                    }
                                    $available = (int) (Medication::find($medicationId)?->quantity ?? 0);
                                    $entered = (int) ($value ?? 0);
                                    if ($entered > $available) {
                                        $fail("La cantidad ingresada ($entered) supera el stock disponible ($available).");
                                    }
                                };
                            })
                            ->helperText(function (Get $get) {
                                $medicationId = $get('medication_id');
                                if (! $medicationId) {
                                    return 'Seleccione un medicamento para ver stock disponible.';
                                }
                                $available = (int) (Medication::find($medicationId)?->quantity ?? 0);
                                return "Disponible: $available";
                            })
                            ->extraAttributes(function (Get $get) {
                                $medicationId = $get('medication_id');
                                $entered = (int) ($get('quantity') ?? 0);
                                $available = (int) (Medication::find($medicationId)?->quantity ?? 0);
                                $exceeds = $medicationId && $entered > $available;
                                return $exceeds ? ['class' => 'border-danger-500 text-danger-600'] : [];
                            })
                            ->afterStateUpdated(fn (Set $set, Get $get) => static::computeTotals($set, $get))
                            ->label('Cantidad'),
                    ])
                    ->minItems(1)
                    ->columns(2)
                    ->createItemButtonLabel('Agregar medicamento')
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set, Get $get) => static::computeTotals($set, $get)),
                Forms\Components\Hidden::make('has_exceeding')->default(false)->dehydrated(false),
                Forms\Components\TextInput::make('total_quantity')
                    ->numeric()
                    ->label('Cantidad total')
                    ->disabled()
                    ->dehydrated(false)
                    ->reactive()
                    ->afterStateHydrated(fn (Set $set, Get $get) => static::computeTotals($set, $get))
                    ->helperText('Se calcula automáticamente a partir de los ítems.'),
                Forms\Components\Textarea::make('reason')
                    ->label('Motivo')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('prescription_image')
                    ->label('Receta')
                    ->image()
                    ->directory('prescriptions')
                    ->nullable(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('items')
                    ->label('Medicamentos')
                    ->formatStateUsing(function ($state, MedicationOutput $record): string {
                        $pairs = $record->items()->with('medication:id,name')->get()
                            ->map(fn ($i) => $i->medication?->name.' ('.$i->quantity.')')
                            ->toArray();
                        return empty($pairs) ? 'N/A' : implode(', ', $pairs);
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('department.name')->label('Departamento'),
                Tables\Columns\BadgeColumn::make('patient_type')->label('Paciente')->colors([
                    'primary' => 'military',
                    'gray' => 'civilian',
                ])->formatStateUsing(fn (string $state) => match ($state) {
                    'military' => 'Militar',
                    'civilian' => 'Civil',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad total')
                    ->state(fn (MedicationOutput $record) => (int) $record->items()->sum('quantity')),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Fecha'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('patient_type')
                    ->label('Tipo de paciente')
                    ->options([
                        'military' => 'Militar',
                        'civilian' => 'Civil',
                    ]),
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
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('Exportar')
                    ->fileName(fn () => 'salidas_'.now()->format('Ymd_His')),
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
            'index' => Pages\ListMedicationOutputs::route('/'),
            'create' => Pages\CreateMedicationOutput::route('/create'),
            'edit' => Pages\EditMedicationOutput::route('/{record}/edit'),
        ];
    }
} 