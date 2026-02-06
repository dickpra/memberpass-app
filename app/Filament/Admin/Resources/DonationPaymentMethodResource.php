<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DonationPaymentMethodResource\Pages;
use App\Filament\Admin\Resources\DonationPaymentMethodResource\RelationManagers;
use App\Models\DonationPaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



class DonationPaymentMethodResource extends Resource
{
    protected static ?string $model = DonationPaymentMethod::class;

    // SETUP NAVIGASI
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Donation Management';
    protected static ?string $navigationLabel = 'Payment Accounts';
    protected static ?int $navigationSort = 2; // Urutan 2

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('method_type')
                            ->options([
                                'paypal' => 'PayPal (Global)',
                                'bank_transfer' => 'Bank Transfer (Local/International)',
                            ])
                            ->required()
                            ->live(), // Penting agar form di bawah bisa berubah realtime

                        Forms\Components\TextInput::make('provider_name')
                            ->label('Provider Name')
                            ->placeholder('e.g. Bank of America or PayPal WFIEd')
                            ->required(),

                        Forms\Components\TextInput::make('currency_code')
                            ->label('Currency')
                            ->placeholder('e.g. USD, IDR, EUR')
                            ->required()
                            ->maxLength(3),
                            // ->uppercase(),

                        Forms\Components\TextInput::make('account_number')
                            ->label('Account No / Email')
                            ->placeholder('12345678 or finance@wfied.org')
                            ->required(),

                        Forms\Components\TextInput::make('account_owner')
                            ->label('Account Holder Name')
                            ->required(),
                            
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('bank-logos')
                            ->columnSpanFull(),
                    ])->columns(2),

                // --- BAGIAN KHUSUS BANK (MUNCUL JIKA TYPE = BANK) ---
                Forms\Components\Section::make('International Bank Details')
                    ->description('Wajib diisi lengkap untuk kelancaran transfer internasional.')
                    ->schema([
                        Forms\Components\TextInput::make('swift_code')
                            ->label('SWIFT / BIC Code'),
                        
                        Forms\Components\TextInput::make('bank_city')
                            ->label('Bank Branch City'),

                        Forms\Components\TextInput::make('bank_branch')
                            ->label('Branch Name'),

                        Forms\Components\Textarea::make('owner_address')
                            ->label('Account Holder Registered Address')
                            ->rows(2)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('tax_number')
                            ->label('VAT / NPWP Number'),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('method_type') === 'bank_transfer'), 
                    // ^ Logic: Hanya muncul jika pilih Bank Transfer

                Forms\Components\Section::make('Instructions')
                    ->schema([
                        Forms\Components\Textarea::make('instructions')
                            ->label('Custom Instructions for Donor')
                            ->placeholder('Contoh: Please include your Name in transfer description.')
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->circular(),
                Tables\Columns\TextColumn::make('provider_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('method_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paypal' => 'info',
                        'bank_transfer' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('account_number')->label('No/Email')->copyable(),
                Tables\Columns\TextColumn::make('currency_code')->badge()->color('warning'),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonationPaymentMethods::route('/'),
            'create' => Pages\CreateDonationPaymentMethod::route('/create'),
            'edit' => Pages\EditDonationPaymentMethod::route('/{record}/edit'),
        ];
    }
}
