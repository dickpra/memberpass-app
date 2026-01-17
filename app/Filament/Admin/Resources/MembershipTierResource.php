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
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select; // Import Select
use Filament\Forms\Get; // Import Get untuk ambil data live
use Filament\Forms\Set; // Import Set untuk ubah data live

class MembershipTierResource extends Resource
{
    protected static ?string $model = MembershipTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Settings Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tier Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('e.g. GreenCard'),
                            
                        // --- PRICING TABS (DENGAN KALKULATOR) ---
                        Tabs::make('Pricing')
                            ->tabs([
                                // === TAB 1: USD ===
                                Tabs\Tab::make('USD Pricing')
                                    ->icon('heroicon-m-globe-americas')
                                    ->schema([
                                        // 1. INPUT HARGA NORMAL (MANUAL)
                                        TextInput::make('original_price_usd')
                                            ->label('Original Price / Year (Normal)')
                                            ->helperText('Isi harga ini dulu untuk menghitung diskon.')
                                            ->numeric()
                                            ->prefix('$')
                                            ->default(0)
                                            ->live(onBlur: true) // Update saat kursor pindah
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::calculatePrice($get, $set, 'usd');
                                            }),

                                        // 2. PILIHAN DISKON (HELPER)
                                        Select::make('discount_helper_usd')
                                            ->label('Discount Calculator')
                                            ->options([
                                                '0' => 'No Discount (Normal Price)',
                                                '10' => '10% OFF',
                                                '20' => '20% OFF',
                                                '25' => '25% OFF',
                                                '50' => '50% OFF (Half Price)',
                                                '75' => '75% OFF',
                                            ])
                                            ->default('0')
                                            ->live()
                                            ->dehydrated(false) // Data ini TIDAK disimpan ke database
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::calculatePrice($get, $set, 'usd');
                                            }),

                                        // 3. INPUT HARGA FINAL (OTOMATIS / EDITABLE)
                                        TextInput::make('price_usd')
                                            ->label('Final Base Price (USD)')
                                            ->helperText('Hasil hitungan otomatis. Bisa diedit manual untuk angka cantik (misal $49.99).')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required(),
                                    ]),

                                // === TAB 2: IDR ===
                                Tabs\Tab::make('IDR Pricing')
                                    ->icon('heroicon-m-banknotes')
                                    ->schema([
                                        // 1. INPUT HARGA NORMAL
                                        TextInput::make('original_price_idr')
                                            ->label('Harga Normal Setahun (Coret)')
                                            ->helperText('Isi harga ini dulu sebagai patokan.')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::calculatePrice($get, $set, 'idr');
                                            }),

                                        // 2. PILIHAN DISKON
                                        Select::make('discount_helper_idr')
                                            ->label('Kalkulator Diskon')
                                            ->options([
                                                '0' => 'Harga Normal (Tidak Diskon)',
                                                '10' => 'Diskon 10%',
                                                '25' => 'Diskon 25%',
                                                '50' => 'Diskon 50% (Setengah Harga)',
                                                '70' => 'Diskon 70%',
                                            ])
                                            ->default('0')
                                            ->live()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                self::calculatePrice($get, $set, 'idr');
                                            }),

                                        // 3. HARGA FINAL
                                        TextInput::make('price_idr')
                                            ->label('Base Yearly Price (IDR)')
                                            ->helperText('Otomatis terhitung. Edit manual jika ingin angka cantik (misal 999.000).')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required(),
                                    ]),
                                
                            ])->columnSpanFull(),
                        
                        // --- CARD STYLE ---
                        Forms\Components\Select::make('css_class')
                            ->label('Card Style (Color Theme)')
                            ->options([
                                'green' => 'Green Style (Green)',
                                // 'vip-lifetime' => 'VIP Black/Gold (Special)',
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
                Tables\Columns\TextColumn::make('price_idr')->label('Price (IDR)')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('price_usd')->label('Price (USD)')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('original_price_idr')->label('Original (IDR)')->money('IDR'),
                Tables\Columns\TextColumn::make('original_price_usd')->label('Original (USD)')->money('USD'),
                Tables\Columns\TextColumn::make('css_class')->label('Theme'),
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

    // --- LOGIC KALKULATOR HARGA ---
    protected static function calculatePrice(Get $get, Set $set, string $currency)
    {
        // Tentukan field mana yang mau diambil/diubah berdasarkan currency
        $origField = $currency === 'usd' ? 'original_price_usd' : 'original_price_idr';
        $discField = $currency === 'usd' ? 'discount_helper_usd' : 'discount_helper_idr';
        $targetField = $currency === 'usd' ? 'price_usd' : 'price_idr';

        // Ambil nilai dari form
        $original = (float) $get($origField);
        $discountPercent = (int) $get($discField);

        // Jika harga normal diisi, hitung diskon
        if ($original > 0) {
            $cutAmount = $original * ($discountPercent / 100);
            $final = $original - $cutAmount;
            
            // Masukkan hasil ke kolom harga final
            $set($targetField, $final);
        }
    }
}