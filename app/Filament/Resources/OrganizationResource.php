<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $modelLabel = 'Empresa/Institución';

    protected static ?string $pluralModelLabel = 'Empresas/Instituciones';

    protected static ?string $navigationLabel = 'Empresas/Instituciones';

    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                Forms\Components\Select::make('type')->label('Tipo')->options([
                    'company' => 'Empresa',
                    'institution' => 'Institución',
                ])->required()->native(false),
                Forms\Components\TextInput::make('rnc')->label('RNC')->maxLength(50),
                Forms\Components\TextInput::make('phone')->label('Teléfono')->maxLength(50),
                Forms\Components\TextInput::make('address')->label('Dirección')->maxLength(255),
                Forms\Components\Toggle::make('status')->label('Estado')->default(true),
                Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('type')->label('Tipo')->colors([
                    'primary' => 'company',
                    'success' => 'institution',
                ])->formatStateUsing(fn (string $state) => $state === 'company' ? 'Empresa' : 'Institución'),
                Tables\Columns\TextColumn::make('rnc')->label('RNC')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono'),
                Tables\Columns\TextColumn::make('address')->label('Dirección')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('status')->label('Estado'),
                Tables\Columns\TextColumn::make('created_at')->date('d/m/Y')->label('Creado')->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::guard('web')->user();
        return $user instanceof \App\Models\User ? ($user->isAdmin() || $user->isSupervisor()) : false;
    }

    public static function canCreate(): bool
    {
        $user = Auth::guard('web')->user();
        return $user instanceof \App\Models\User ? ($user->isAdmin() || $user->isSupervisor()) : false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::guard('web')->user();
        return $user instanceof \App\Models\User ? ($user->isAdmin() || $user->isSupervisor()) : false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::guard('web')->user();
        return $user instanceof \App\Models\User ? ($user->isAdmin() || $user->isSupervisor()) : false;
    }
}
