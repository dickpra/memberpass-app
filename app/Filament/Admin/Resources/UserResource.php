<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Member Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- BAGIAN 1: DATA DIRI (BOLEH DIEDIT) ---
                Forms\Components\Section::make('Personal Information')
                    ->description('Data identitas member (Bisa dikoreksi jika ada typo)')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('country'),
                        Forms\Components\TextInput::make('organization'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state)) // Hanya update jika diisi
                            ->required(fn (string $context): bool => $context === 'create'),
                        // Password field (biarkan opsional/hidden saat edit)
                    ])->columns(2),

                // --- BAGIAN 2: STATUS MEMBERSHIP (TERKUNCI / READ ONLY) ---
                Forms\Components\Section::make('Membership Status')
                    ->description('Data ini dikelola otomatis oleh sistem pembayaran. Jangan ubah manual kecuali lewat Action.')
                    ->schema([
                        Forms\Components\TextInput::make('member_id')
                            ->label('Member ID')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('membership_type')
                            ->label('Current Tier')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            // ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                            ->disabled()
                            ->dehydrated(false)
                            ->extraInputAttributes(fn ($record) => [
                                'class' => match ($record?->status) {
                                    'active' => 'text-green-600 font-bold',
                                    'waiting_payment' => 'text-yellow-600 font-bold',
                                    default => 'text-gray-600',
                                }
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('join_date')
                                    ->disabled(),
                                Forms\Components\DatePicker::make('expiry_date')
                                    ->label('Valid Until')
                                    ->disabled(),
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_id')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                
                // Badge Status Keren
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'waiting_admin_decision' => 'warning', // Status request Gold
                        'payment_rejected' => 'danger',
                        'inactive' => 'danger',
                        'banned' => 'danger',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('membership_type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        // 'Gold' => 'warning', // Kuning Emas
                        // 'Silver' => 'gray',
                        // 'Bronze' => 'danger', // Coklat/Orange
                        'VIP Lifetime' => 'warning', // Emas
                        'GreenCard' => 'success', // Hijau
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Expired Date')
                    // Merahkan tanggal jika sudah lewat hari ini
                    ->color(fn ($state) => $state < now() ? 'danger' : 'gray'),
            ])
            ->filters([
                // Filter Status
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'waiting_admin_decision' => 'Requesting Gold/Verif',
                        'registered' => 'Registered',
                        'banned' => 'Banned',
                        'inactive' => 'Inactive',
                ])
                ->label('Filter by Status'),

            // --- FILTER TAHUN EXPIRED ---
            // Memudahkan cek siapa yang habis tahun ini
            Tables\Filters\Filter::make('expired_this_year')
                ->query(fn (Builder $query) => $query->whereYear('expiry_date', date('Y')))
                ->label('Expired Tahun Ini'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),

                // --- ACTION 1: APPROVE GOLD REQUEST ---
                // Khusus user yang statusnya 'waiting_admin_decision' & Tier Gold
                // 2. GROUP ACTION (Agar Rapi)
            Tables\Actions\ActionGroup::make([
                
                // --- A. BAGIAN VIP MANAGEMENT ---
                
                // Action: ANGKAT JADI VIP (Promote)
                Tables\Actions\Action::make('promote_vip')
                    ->label('Promote to VIP Lifetime')
                    ->icon('heroicon-o-trophy')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Promote to VIP?')
                    ->modalDescription('User akan mendapatkan akses seumur hidup (Auto-Renew).')
                    // Muncul jika user BUKAN VIP
                    ->visible(fn ($record) => $record->membership_type !== 'VIP Lifetime')
                    ->action(function ($record) {
                        
                        // LOGIKA BARU: TEMPEL PREFIX "VIP-"
                        // Ambil ID lama: WFIED-26ABCDE
                        $currentId = $record->member_id;
                        
                        // Cek jaga-jaga, kalau belum ada "VIP-", baru tambahkan
                        if (!str_starts_with($currentId, 'VIP-')) {
                            $newVipId = 'VIP-' . $currentId; // Hasil: VIP-WFIED-26ABCDE
                        } else {
                            $newVipId = $currentId; // Sudah VIP, biarkan
                        }

                        $record->update([
                            'membership_type' => 'VIP Lifetime',
                            'status' => 'active',
                            'join_date' => now(),
                            'expiry_date' => \Carbon\Carbon::now()->endOfYear(),
                            
                            // SIMPAN ID HASIL MODIFIKASI
                            'member_id' => $newVipId, 
                        ]);
                        \Filament\Notifications\Notification::make()->title('User is now VIP!')->success()->send();
                    }),

                // Action: TURUNKAN DARI VIP (Demote)
                Tables\Actions\Action::make('demote_vip')
                    ->label('Demote to GreenCard')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Cabut Status VIP?')
                    ->modalDescription('User akan dikembalikan menjadi member biasa (GreenCard).')
                    // Muncul jika user ADALAH VIP
                    ->visible(fn ($record) => $record->membership_type === 'VIP Lifetime')
                    ->action(function ($record) {
                        
                        // LOGIKA BARU: COPOT PREFIX "VIP-"
                        // Ambil ID VIP: VIP-WFIED-26ABCDE
                        $currentId = $record->member_id;
                        
                        // Hapus tulisan "VIP-" agar kembali jadi WFIED-26ABCDE
                        $originalId = str_replace('VIP-', '', $currentId);

                        $record->update([
                            'membership_type' => 'GreenCard',
                            
                            // KEMBALIKAN KE ID ASLI
                            'member_id' => $originalId, 
                        ]);
                        \Filament\Notifications\Notification::make()->title('VIP Status Revoked.')->success()->send();
                    }),

                // --- B. BAGIAN STATUS ACCESS (ON/OFF) ---

                // Action: NONAKTIFKAN (Freeze/Suspend)
                Tables\Actions\Action::make('deactivate_member')
                    ->label('Deactivate / Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan Member?')
                    ->modalDescription('User tidak akan bisa mengakses fitur member, tapi data tidak dihapus.')
                    // Muncul jika statusnya Active
                    ->visible(fn ($record) => $record->status === 'active')
                    ->action(function ($record) {
                        $record->update(['status' => 'inactive']);
                        \Filament\Notifications\Notification::make()->title('Member Deactivated')->warning()->send();
                    }),
                    // --- Action: BAN MEMBER (Suspend) ---
                Tables\Actions\Action::make('ban_member')
                    ->label('Ban / Suspend User') // Ganti label biar jelas
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Blokir User Ini?')
                    ->modalDescription('User akan berstatus BANNED. Mereka tidak bisa login atau memperpanjang membership sampai Anda mengaktifkannya lagi.')
                    // Muncul jika status BUKAN banned
                    ->visible(fn ($record) => $record->status !== 'banned')
                    ->action(function ($record) {
                        // SET STATUS JADI 'banned'
                        $record->update(['status' => 'banned']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('User Banned Successfully')
                            ->body('User kini tidak memiliki akses apapun.')
                            ->danger()
                            ->send();
                    }),

                // --- Action: UNBAN / ACTIVATE (Restore) ---
                Tables\Actions\Action::make('activate_member')
                    ->label('Unban / Activate')
                    ->icon('heroicon-o-check-circle') // Ganti ikon centang
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Kembali User?')
                    ->modalDescription('User akan kembali aktif dan bisa mengakses dashboard.')
                    // Muncul jika status TIDAK active (bisa inactive atau banned)
                    ->visible(fn ($record) => $record->status !== 'active' && $record->status !== 'banned')
                    ->action(function ($record) {
                        $tier = $record->membership_type ?: 'GreenCard';
                        
                        $record->update([
                            'status' => 'active', // Kembalikan ke active
                            'membership_type' => $tier,
                            // Opsional: Reset expired ke akhir tahun ini jika perlu
                            // 'expiry_date' => \Carbon\Carbon::now()->endOfYear(), 
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('User Activated')
                            ->success()
                            ->send();
                    }),
                // --- ACTION 2: MANUAL EXTEND (PERPANJANG MANUAL) ---
                // Berguna jika ada masalah sistem atau bonus perpanjangan
                Tables\Actions\Action::make('manual_extend')
                    ->label('Extend +1 Year')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'active') // Hanya yang sudah aktif
                    ->action(function ($record) {
                        $record->update([
                            'expiry_date' => $record->expiry_date->addYear(),
                        ]);
                        \Filament\Notifications\Notification::make()->title('Expiry Extended 1 Year')->success()->send();
                    }),

                // Action: AKTIFKAN KEMBALI (Re-Activate)
                Tables\Actions\Action::make('activate_member')
                    ->label('Activate Manual')
                    ->icon('heroicon-o-power')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Member Manual?')
                    ->modalDescription('User akan diaktifkan kembali sampai akhir tahun.')
                    // Muncul jika statusnya TIDAK Active
                    ->visible(fn ($record) => $record->status !== 'active')
                    ->action(function (User $record) { // Type hint User agar autocomplete jalan
                        // 1. Tentukan Tier (Default GreenCard jika kosong)
                        $tier = $record->membership_type ?: 'GreenCard';

                        // 2. LOGIKA ID: Cek apakah sudah punya ID?
                        $finalMemberId = $record->member_id;

                        if (empty($finalMemberId)) {
                            // Jika KOSONG, generate baru pakai fungsi static di Model
                            // Kita cek apakah tier mengandung kata 'vip' untuk parameter $isVip
                            $isVip = str_contains(strtolower($tier), 'vip');
                            
                            $finalMemberId = User::generateMemberId($isVip);
                        } 
                        // Jika TIDAK KOSONG (else), $finalMemberId tetap pakai nilai lama ($record->member_id)

                        // 3. Update Database
                        $record->update([
                            'status' => 'active',
                            'membership_type' => $tier,
                            'join_date' => $record->join_date ?? now(),
                            'expiry_date' => \Carbon\Carbon::now()->endOfYear(),
                            'member_id' => $finalMemberId, // Update ID (Entah itu baru atau lama)
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Member Activated')
                            ->body("Status active. ID: $finalMemberId")
                            ->success()
                            ->send();
                    }),

            ])
            ->label('Actions')
            ->icon('heroicon-m-ellipsis-vertical')
            ->color('primary')
            ->button(), // Opsional: Tampilan tombol titik tiga
        ]);
    }
    
    public static function getRelations(): array
    {
        return [
            // Nanti kita tambah relasi Payment di sini
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}