<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MembershipTierResource\Pages;
use App\Filament\Admin\Resources\MembershipTierResource\RelationManagers;
use App\Models\MembershipTier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MembershipTierResource extends Resource
{
    protected static ?string $model = MembershipTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tier Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->placeholder('e.g. Silver'),
                            
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('IDR')
                            ->required(),
                            
                        Forms\Components\Select::make('css_class')
                            ->label('Card Style (Color Theme)')
                            ->options([
                                // 'bronze' => 'Bronze Style (Orange)',
                                // 'silver' => 'Silver Style (Gray)',
                                'gold' => 'Gold Style (Yellow)',
                                'green' => 'Green Style (Green)',
                                // 'platinum' => 'Platinum Style (Black/Blue)', // Bisa tambah CSS nanti
                            ])
                            ->required(),

                        Forms\Components\Toggle::make('is_invitation_only')
                            ->label('Invitation Only (Cannot be bought directly)')
                            ->default(false),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Tier Benefits')
                    ->description('List fasilitas yang akan muncul di kartu membership')
                    ->schema([
                        // REPEATER: Admin bisa tambah point sesuka hati
                        Forms\Components\Repeater::make('benefits')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Benefit Item')
                                    ->required(),
                            ])
                            ->simple(
                                Forms\Components\TextInput::make('text')->required()
                            )
                            ->addActionLabel('Add Benefit')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->money('IDR'),
                Tables\Columns\IconColumn::make('is_invitation_only')->boolean()->label('Invite Only'),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipTiers::route('/'),
            'create' => Pages\CreateMembershipTier::route('/create'),
            'edit' => Pages\EditMembershipTier::route('/{record}/edit'),
        ];
    }
}
