<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    // Ikon Menu (Gedung Bank)
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    
    // Label Menu
    protected static ?string $navigationLabel = 'Bank Accounts';
    protected static ?string $modelLabel = 'Rekening Bank';
    protected static ?int $navigationSort = 2; // Urutan menu

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECTION 1: INFORMASI UTAMA
                Forms\Components\Section::make('Informasi Rekening')
                    ->description('Masukkan detail rekening tujuan transfer.')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->placeholder('Contoh: BCA / MANDIRI / CHASE')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Rekening')
                            ->required()
                            ->numeric() // Pastikan angka
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('account_owner')
                            ->label('Atas Nama (Holder Name)')
                            ->required()
                            ->columnSpanFull(), // Lebar penuh
                    ])->columns(2),

                // SECTION 2: DETAIL INTERNASIONAL (Opsional)
                Forms\Components\Section::make('International Details (Opsional)')
                    ->description('Isi jika menerima transfer dari luar negeri.')
                    ->collapsed() // Default tertutup biar rapi
                    ->schema([
                        Forms\Components\TextInput::make('bank_city')
                            ->label('Bank City / Branch')
                            ->placeholder('Contoh: KCU Sudirman Jakarta'),

                        Forms\Components\TextInput::make('swift_code')
                            ->label('SWIFT / BIC Code')
                            ->placeholder('Contoh: CENAIDJA')
                            ->helperText('Kode unik bank untuk transaksi internasional.'),
                    ])->columns(2),

                // SECTION 3: TAMPILAN & STATUS
                Forms\Components\Section::make('Tampilan')
                    ->schema([
                        // Forms\Components\FileUpload::make('logo')
                        //     ->label('Logo Bank')
                        //     ->image()
                        //     ->directory('bank-logos') // Disimpan di storage/app/public/bank-logos
                        //     ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Rekening Ini')
                            ->default(true)
                            ->helperText('Jika dimatikan, rekening tidak akan muncul di dashboard member.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Logo
                // Tables\Columns\ImageColumn::make('logo')
                //     ->circular()
                //     ->defaultImageUrl(url('/images/default-bank.png')), // Fallback image jika perlu

                // 2. Nama Bank
                Tables\Columns\TextColumn::make('bank_name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                // 3. Info Detail (No Rek & SWIFT)
                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->copyable() // Admin bisa klik copy
                    ->copyMessage('Nomor rekening disalin')
                    ->description(fn (BankAccount $record) => $record->swift_code ? 'SWIFT: ' . $record->swift_code : null),

                // 4. Atas Nama
                Tables\Columns\TextColumn::make('account_owner')
                    ->label('A.N'),

                // 5. Status Switch
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active?'),
            ])
            ->filters([
                // Filter Aktif/Nonaktif
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Hanya yang Aktif'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}