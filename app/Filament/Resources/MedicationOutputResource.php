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
                Forms\Components\Select::make('patient_type')
                    ->searchable()
                    ->label('Tipo de paciente')
                    ->options([
                        'military' => 'Militar',
                        'civilian' => 'Civil',
                        'department' => 'Departamento',
                    ])
                    ->native(false)
                    ->reactive()
                    ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                    ->required(),
                Forms\Components\Select::make('department_id')
                    ->label('Departamento destino')
                    ->relationship('department', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->disabled(fn (Get $get) => in_array($get('patient_type'), ['military', 'civilian'], true))
                    ->required(fn (Get $get) => $get('patient_type') === 'department')
                    ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                    ->default(fn () => Department::where('code', 'FAR')->value('id') ?? Department::where('name', 'Farmacia')->value('id') ?? null)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción'),
                        Forms\Components\Toggle::make('status')
                            ->label('Estado')
                            ->default(true),
                    ])
                    ->createOptionUsing(fn (array $data) => Department::create($data)->getKey()),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('patient_external_id')
                            ->label('Cédula militar')
                            ->hint('Digite cédula y presione fuera para buscar')
                            ->mask('999-9999999-9')
                            ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                            ->required(fn (Get $get) => $get('patient_type') === 'military')
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if ($value === null || $value === '') {
                                        return;
                                    }
                                    $digits = preg_replace('/\D+/', '', (string) $value);
                                    if (strlen($digits) !== 11) {
                                        $fail('Cédula inválida. Debe tener 11 dígitos.');
                                    }
                                };
                            })
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $armada = app(\App\Services\ArmadaApi::class);
                                $ard = app(\App\Services\ARD::class);
                                $dir = app(\App\Services\MilitaryDirectory::class);
                                $name = null;
                                $digits = preg_replace('/\D+/', '', (string) $state);
                                if (strlen($digits) !== 11) {
                                    return; // no lookup until cedula is valid
                                }
                                // 1) Armada API
                                $p = $armada->getPerson($digits);
                                $name = is_array($p) ? $armada->formatName($p) : null;
                                if ($ard->isConfigured()) {
                                    $p = $name ? null : $ard->getPerson($digits);
                                    $name = $name ?: (is_array($p) ? $ard->formatName($p) : null);
                                }
                                if (! $name) {
                                    $name = $dir->getDisplayName($digits);
                                }
                                if ($name) {
                                    $set('patient_name', $name.' ('.$digits.')');
                                }
                            })
                            ->visible(fn (Get $get) => $get('patient_type') === 'military')
                            ,
                        Forms\Components\TextInput::make('patient_name')
                            ->label('Nombre del paciente')
                            ->placeholder('Para pacientes civiles o relleno automático')
                            ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                            ->visible(fn (Get $get) => in_array($get('patient_type'), ['civilian','military']))
                            ->required(fn (Get $get) => in_array($get('patient_type'), ['civilian','military']))
                            ->helperText('Para militar se rellenará como Nombre Apellido (cédula).'),
                        Forms\Components\TextInput::make('doctor_external_id')
                            ->label('Cédula médico')
                            ->mask('999-9999999-9')
                            ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if ($value === null || $value === '') {
                                        return;
                                    }
                                    $digits = preg_replace('/\D+/', '', (string) $value);
                                    if (strlen($digits) !== 11) {
                                        $fail('Cédula inválida. Debe tener 11 dígitos.');
                                    }
                                };
                            })
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $armada = app(\App\Services\ArmadaApi::class);
                                $ard = app(\App\Services\ARD::class);
                                $dir = app(\App\Services\MilitaryDirectory::class);
                                $name = null;
                                $digits = preg_replace('/\D+/', '', (string) $state);
                                if (strlen($digits) !== 11) {
                                    return; // no lookup until cedula is valid
                                }
                                // 1) Armada API
                                $p = $armada->getPerson($digits);
                                $name = is_array($p) ? $armada->formatName($p) : null;
                                if ($ard->isConfigured()) {
                                    $p = $name ? null : $ard->getPerson($digits);
                                    $name = $name ?: (is_array($p) ? $ard->formatName($p) : null);
                                }
                                if (! $name) {
                                    $name = $dir->getDisplayName($digits);
                                }
                                if ($name) {
                                    $set('doctor_name', $name.' ('.$digits.')');
                                }
                            })
                            ->helperText('Opcional: se rellenará como Nombre Apellido (cédula).')
                            ->visible(fn (Get $get) => in_array($get('patient_type'), ['civilian','military'])),
                        Forms\Components\TextInput::make('doctor_name')
                            ->label('Nombre del médico')
                            ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                            ->visible(fn (Get $get) => in_array($get('patient_type'), ['civilian','military']))
                            ->helperText('Opcional: puede escribirlo o se autocompleta por cédula.'),
                    ])->columns(2),
                Forms\Components\Repeater::make('items')
                    ->label('Medicamentos')
                    ->schema([
                        Forms\Components\Hidden::make('id'),
                        Forms\Components\Select::make('medication_id')
                            ->label('Medicamento')
                            ->options(fn () => Medication::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
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
                                $attrs = ['x-on:keydown.enter.stop.prevent' => ''];
                                if ($exceeds) {
                                    $attrs['class'] = 'border-danger-500 text-danger-600';
                                }
                                return $attrs;
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
                    ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
                    ->afterStateHydrated(fn (Set $set, Get $get) => static::computeTotals($set, $get))
                    ->helperText('Se calcula automáticamente a partir de los ítems.'),
                Forms\Components\Textarea::make('reason')
                    ->label('Motivo')
                    ->extraAttributes(['x-on:keydown.enter.stop.prevent' => ''])
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
                    'success' => 'department',
                ])->formatStateUsing(fn (string $state) => match ($state) {
                    'military' => 'Militar',
                    'civilian' => 'Civil',
                    'department' => 'Departamento',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Total')
                    ->state(fn (MedicationOutput $record) => (int) $record->items()->sum('quantity')),
                Tables\Columns\TextColumn::make('created_at')->date('d/m/Y')->label('Fecha'),
                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Paciente nombre')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('doctor_name')
                    ->label('Médico')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('patient_type')
                    ->label('Tipo de salida')
                    ->options([
                        'military' => 'Militar',
                        'civilian' => 'Civil',
                        'department' => 'Departamento',
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
                Tables\Filters\Filter::make('department')
                    ->form([
                        Forms\Components\Select::make('department_id')
                            ->label('Departamento')
                            ->options(fn () => \App\Models\Department::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false),
                    ])
                    ->query(fn ($query, array $data) => $query->when($data['department_id'] ?? null, fn ($q, $id) => $q->where('department_id', $id))),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\MedicationOutputExporter::class)
                    ->label('Exportar')
                    ->fileName(fn () => 'salidas_'.now()->format('Ymd_His')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ticket')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer')
                    ->url(fn (MedicationOutput $record) => route('tickets.outputs.show', $record))
                    ->openUrlInNewTab(),
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