<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\GeneralSetting;


class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Filter Global: Admin tidak perlu lihat user yang baru klik pilih paket tapi belum bayar
        return parent::getEloquentQuery()
            ->where('status', '!=', 'pending_upload');
    }
    
    // Tampilkan jumlah notifikasi di sidebar jika ada yang waiting
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'waiting_verification')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        // Ambil settingan bank untuk ditampilkan (Read Only)
        $settings = GeneralSetting::first(); 

        return $form
            ->schema([
                // --- KOLOM KIRI: INVOICE & BANK INFO ---
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Invoice Information')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Placeholder::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->content(fn ($record) => '#INV-' . $record->created_at->format('Y') . '-' . str_pad($record->id, 5, '0', STR_PAD_LEFT)),

                                Forms\Components\Placeholder::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->content(fn ($record) => $record->created_at->format('d F Y')),
                                
                                Forms\Components\Placeholder::make('total_amount')
                                    ->label('Total Amount Due')
                                    ->content(fn ($record) => $record->currency . ' ' . number_format($record->amount, 2))
                                    ->extraAttributes(['class' => 'text-xl font-bold text-primary-600']),
                            ]),

                        Forms\Components\Section::make('Our Bank Details')
                            ->description('Informasi rekening yang ditampilkan ke member')
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                Forms\Components\Placeholder::make('bank_name')
                                    ->label('Bank Name')
                                    ->content($settings->bank_name ?? '-'),
                                    
                                Forms\Components\Placeholder::make('account_number')
                                    ->label('Account Number')
                                    ->content($settings->bank_account_number ?? '-')
                                    ->extraAttributes(['class' => 'font-mono font-bold']),
                                    
                                Forms\Components\Placeholder::make('account_owner')
                                    ->label('Beneficiary Name')
                                    ->content($settings->bank_account_owner ?? '-'),

                                Forms\Components\Placeholder::make('swift')
                                    ->label('SWIFT Code')
                                    ->visible((bool) $settings->bank_swift_code)
                                    ->content($settings->bank_swift_code),
                            ]),
                    ])->columnSpan(1),

                // --- KOLOM TENGAH: MEMBER DETAILS ---
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Member Profile')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('member_name')
                            ->label('Full Name')
                            // PERBAIKAN: Gunakan formatStateUsing untuk mengambil data relasi user
                            ->formatStateUsing(fn ($record) => $record->user->name ?? '-') 
                            ->disabled()
                            ->dehydrated(false), // PENTING: Jangan simpan ke database (karena ini cuma display)

                        Forms\Components\TextInput::make('member_email')
                            ->label('Email Address')
                            ->formatStateUsing(fn ($record) => $record->user->email ?? '-')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('member_phone')
                            ->label('Phone / WhatsApp')
                            ->formatStateUsing(fn ($record) => $record->user->phone ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                            
                        Forms\Components\TextInput::make('member_country')
                            ->label('Country')
                            ->formatStateUsing(fn ($record) => $record->user->country ?? '-')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('member_org')
                            ->label('Organization')
                            ->formatStateUsing(fn ($record) => $record->user->organization ?? '-')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Forms\Components\Section::make('Membership Plan')
                    ->schema([
                        // 1. Tipe Membership (Tier)
                        Forms\Components\TextInput::make('plan_type')
                            ->label('Selected Tier')
                            ->formatStateUsing(fn ($record) => $record->user->membership_type ?? '-')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraInputAttributes(['class' => 'font-bold text-lg text-primary-600'])
                            ->columnSpanFull(), // Biarkan memanjang penuh

                        // 2. Grid untuk Tanggal Aktif & Expired
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('active_since')
                                    ->label('Active Since')
                                    ->formatStateUsing(fn ($record) => $record->user->join_date 
                                        ? \Carbon\Carbon::parse($record->user->join_date)->format('d M Y') 
                                        : '-'
                                    )
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('valid_until')
                                    ->label('Valid Until (Expired)')
                                    ->formatStateUsing(fn ($record) => $record->user->expiry_date 
                                        ? \Carbon\Carbon::parse($record->user->expiry_date)->format('d M Y') 
                                        : '-'
                                    )
                                    ->disabled()
                                    ->dehydrated(false)
                                    // Fitur Visual: Merah jika expired, Hijau jika masih aktif
                                    ->extraInputAttributes(fn ($record) => [
                                        'class' => ($record->user->expiry_date && \Carbon\Carbon::parse($record->user->expiry_date)->isPast()) 
                                            ? 'font-bold text-red-600' 
                                            : 'font-bold text-green-600'
                                    ]),
                            ]),
                    ]),
                        ])->columnSpan(1),

                // --- KOLOM KANAN: VERIFIKASI & BUKTI ---
                // --- KOLOM KANAN: VERIFIKASI ---
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Verification Action')
                            ->icon('heroicon-o-check-badge')
                            ->schema([
                                // 1. Update disabled pada Select Status
                        // GANTI Select::make('status') DENGAN INI:
                Forms\Components\Placeholder::make('status_display')
                    ->label('Current Status')
                    ->content(fn ($record) => match ($record->status) {
                        'approved' => '✅ APPROVED',
                        'rejected' => '❌ REJECTED',
                        default => '⚠️ WAITING VERIFICATION',
                    })
                    ->extraAttributes(fn ($record) => [
                        'class' => match ($record->status) {
                            'approved' => 'text-xl font-bold text-green-600',
                            'rejected' => 'text-xl font-bold text-red-600',
                            default => 'text-xl font-bold text-yellow-600',
                        }
                    ]),

                // UPDATE NOTE
                // Forms\Components\Textarea::make('admin_note')
                //     ->label('Reason / Note')
                //     ->disabled() // Read Only
                //     ->rows(3),

                        // 2. Update disabled pada Admin Note
                        Forms\Components\Textarea::make('admin_note')
                            ->rows(3)
                            ->disabled(fn ($record) => $record && in_array($record->status, ['approved', 'rejected'])),
                            ]),

                        Forms\Components\Section::make('Transfer Proofs')
                            ->schema([
                                Forms\Components\TextInput::make('sender_name')
                                    ->label('Sender Name (Input User)')
                                    ->disabled(), // Ini memang selalu disabled
                                    
                                Forms\Components\Repeater::make('files')
                                    ->relationship()
                                    ->hiddenLabel()
                                    ->schema([
                                        Forms\Components\FileUpload::make('file_path')
                                            ->disk('public')
                                            ->directory('payment-proofs') // Hanya fallback, aslinya pakai logic member
                                            ->image()
                                            ->openable()
                                            ->downloadable()
                                            ->deletable(false) // Admin tidak boleh hapus
                                            ->columnSpanFull(),
                                    ])
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->grid(1),
                            ]),
                    ])->columnSpan(1),

            ])->columns(3)
            // GEMBOK GLOBAL (OPSIONAL TAPI KUAT):
            // Jika Payment sudah Approved, matikan tombol "Save Changes" di bawah.
            // Jadi Admin benar-benar hanya bisa lihat (View Only).
            ->disabled(fn ($record) => $record && in_array($record->status, ['approved', 'rejected']));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y, H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->user->email),

                Tables\Columns\TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registration' => 'info',
                        'renewal' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        // Jika User minta batal, timpa label statusnya
                        if ($record->user && $record->user->status === 'cancellation_requested') {
                            return 'Request Cancel';
                        }
                        return ucfirst(str_replace('_', ' ', $state));
                    })
                    ->color(fn (string $state, $record): string => match (true) {
                        // Prioritas warna merah jika user minta batal
                        ($record->user && $record->user->status === 'cancellation_requested') => 'danger',
                        $state === 'approved' => 'success',
                        $state === 'waiting_verification' => 'warning',
                        $state === 'rejected' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // KOSONGKAN FILTER DI SINI
                // Karena kita akan pakai Tabs di ListPayments.php
            ])
            ->actions([
                // Tombol Review yang sudah ada
                Tables\Actions\EditAction::make()->label('Review'),

                // --- TAMBAHAN: APPROVE CANCEL DARI PAYMENT ---
                Tables\Actions\Action::make('approve_cancel')
                    ->label('Approve Cancel')
                    ->icon('heroicon-o-x-circle') // Icon silang bulat
                    ->color('danger') // Warna merah
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pembatalan?')
                    ->modalDescription('Tagihan ini akan dihapus dan User akan di-reset ke status Registered (bisa pilih paket ulang).')
                    ->modalSubmitActionLabel('Ya, Batalkan')
                    
                    // Hanya muncul jika status User adalah 'cancellation_requested'
                    ->visible(fn (Payment $record) => $record->user && $record->user->status === 'cancellation_requested')
                    
                    ->action(function (Payment $record) {
                        $user = $record->user;
                        
                        // 1. Reset Status User
                        if ($user) {
                            $user->update([
                                'status' => 'registered',
                                'membership_type' => null, // Hapus pilihan paketnya
                            ]);
                        }

                        // 2. Hapus Data Payment ini (karena batal)
                        $record->delete();

                        // 3. Notifikasi
                        \Filament\Notifications\Notification::make()
                            ->title('Payment cancelled & User reset.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}