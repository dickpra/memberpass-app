<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DonationProgramResource\Pages;
use App\Filament\Admin\Resources\DonationProgramResource\RelationManagers;
use App\Models\DonationProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class DonationProgramResource extends Resource
{
   protected static ?string $model = DonationProgram::class;
    
    // SETUP NAVIGASI
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Donation Management';
    protected static ?int $navigationSort = 1; // Urutan 1

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Program Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\FileUpload::make('banner_image')
                            ->image()
                            ->directory('donation-banners')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Target & Status')
                    ->schema([
                        Forms\Components\TextInput::make('target_amount')
                            ->label('Target Amount (Optional)')
                            ->numeric()
                            ->prefix('$'),
                        
                        Forms\Components\TextInput::make('target_currency')
                            ->default('USD')
                            ->disabled() // Kita kunci USD sebagai standar laporan global
                            ->dehydrated(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Publish Program')
                            ->default(true)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_image')->circular(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_amount')->money('USD')->label('Target'),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->filters([
                // HAPUS TrashedFilter::make() DARI SINI
                // Karena sudah digantikan oleh Tabs di atas
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // 1. ACTION DELETE BIASA (SOFT DELETE)
                Tables\Actions\DeleteAction::make()
                    ->label('Delete') // Kembali ke istilah umum
                    ->icon('heroicon-m-trash')
                    // EDUKASI USER DI SINI:
                    ->modalHeading('Pindahkan ke Sampah?')
                    ->modalDescription('Program ini akan dipindahkan ke Tab "Sampah". Riwayat donasi member TETAP AMAN. Anda bisa mengembalikannya (Restore) nanti.')
                    ->modalSubmitActionLabel('Ya, Pindahkan ke Sampah'),

                // 2. ACTION RESTORE (KEMBALIKAN)
                Tables\Actions\RestoreAction::make()
                    ->label('Restore (Kembalikan)')
                    ->color('success')
                    ->modalHeading('Kembalikan Program Ini?')
                    ->modalDescription('Program akan kembali muncul di daftar Active.'),
                
                // 3. ACTION FORCE DELETE (HAPUS PERMANEN)
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->modalHeading('⚠️ Hapus Permanen Program?')
                    ->modalDescription('PERINGATAN: Program ini akan dihapus SELAMANYA beserta SELURUH RIWAYAT DONASI & BUKTI TRANSFER terkait. Data yang dihapus TIDAK BISA KEMBALI.')
                    ->modalSubmitActionLabel('Saya Paham, Hapus Selamanya'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(), // Tambahkan ini juga
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonationPrograms::route('/'),
            'create' => Pages\CreateDonationProgram::route('/create'),
            'edit' => Pages\EditDonationProgram::route('/{record}/edit'),
        ];
    }

    // Tambahkan ini agar Soft Delete bekerja sempurna di Filament
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
