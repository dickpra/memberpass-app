<?php

namespace App\Filament\Member\Resources;

use App\Filament\Member\Resources\MemberPaymentHistoryResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MemberPaymentHistoryResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationLabel = 'Payment History';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $slug = 'payment-history';

    // Filter hanya data milik user login
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');
    }

    // FORM INI AKAN MUNCUL SAAT KLIK TOMBOL "VIEW" (MATA)
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Total Amount')
                                    ->prefix(fn ($record) => $record?->currency === 'USD' ? 'USD' : 'IDR')
                                    ->numeric()
                                    ->readOnly(), // Kunci jadi Read Only

                                Forms\Components\TextInput::make('status')
                                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                                    ->readOnly(),
                                
                                Forms\Components\TextInput::make('type')
                                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                                    ->readOnly(),

                                Forms\Components\TextInput::make('created_at')
                                    ->label('Date Created')
                                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d M Y H:i'))
                                    ->readOnly(),
                            ]),
                        
                        // Menampilkan Bukti Transfer yang sudah diupload
                        // Forms\Components\FileUpload::make('proof_of_transfer')
                        //     ->label('Uploaded Proof')
                        //     ->image()
                        //     ->disk('public') // Sesuaikan disk kamu
                        //     ->visibility('public')
                        //     ->downloadable() // Member bisa download bukti mereka sendiri
                        //     ->openable()
                        //     ->disabled() // Tidak bisa ganti/hapus
                        //     ->dehydrated(false)
                        //     ->columnSpanFull(),

                        Forms\Components\Textarea::make('admin_note')
                            ->label('Note from Admin')
                            ->rows(3)
                            ->readOnly()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('Curr')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'USD' => 'success', // Hijau kalau Dollar
                        'IDR' => 'info',    // Biru kalau Rupiah
                        default => 'gray',
                    })
                ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending_upload' => 'warning',
                        'waiting_verification' => 'info',
                        default => 'gray',
                    }),
                
                 Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                // 1. TOMBOL VIEW (MATA) - Membuka Form di atas dalam Modal
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Transaction Details')
                    ->color('info'),

                // 2. TOMBOL DOWNLOAD INVOICE
                Tables\Actions\Action::make('download_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn (Payment $record) => route('invoice.print', ['payment' => $record->id]))
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMemberPaymentHistories::route('/'),
        ];
    }

    // Tetap matikan Create/Delete agar member tidak bisa manipulasi
    public static function canCreate(): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    // Izinkan View tapi Form-nya ReadOnly
    public static function canView(Model $record): bool { return true; } 
}