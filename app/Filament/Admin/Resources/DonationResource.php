<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DonationResource\Pages;
use App\Filament\Admin\Resources\DonationResource\RelationManagers;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportComputed\CannotCallComputedDirectlyException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; // Import iniuse Illuminate\Support\Str;
use Illuminate\Support\Str;


class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    // SETUP NAVIGASI
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Donation Management';
    protected static ?string $navigationLabel = 'Incoming Donations';
    protected static ?int $navigationSort = 3; // Urutan 3

    
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        // Badge merah jika ada donasi pending
        return static::getModel()::where('status', 'pending_verification')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Donation Info')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->disabled(), // Admin gak boleh edit pengirim
                                
                                Forms\Components\Select::make('donation_program_id')
                                    ->relationship('program', 'title')
                                    ->disabled(),

                                Forms\Components\TextInput::make('amount')
                                    ->prefix(fn ($record) => $record->currency)
                                    ->disabled(),

                                Forms\Components\TextInput::make('sender_name')
                                    ->label('Sender Name (From Proof)')
                                    ->disabled(),
                            ])->columns(2),

                        Forms\Components\Section::make('Verification')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending_verification' => 'Pending Verification',
                                        'approved' => 'Approved (Verified)',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->required(),

                                Forms\Components\Textarea::make('admin_note')
                                    ->label('Admin Note (Reason if rejected)'),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\FileUpload::make('proof_file')
                            ->label('Proof of Transfer')
                            
                            ->image()
                                    ->directory('donation-proofs')
                                    ->disk('secure') // <--- GANTI DISK KE SECURE
                                    ->visibility('private') // <--- SET VISIBILITY
                                    ->downloadable()
                                    ->openable() // Bisa di-zoom
                                    ->disabled() // Admin hanya lihat, tidak edit
                            // 1. DISK SECURE (Wajib)
                            ->disk('secure')
                            ->visibility('private')

                            // 2. FOLDER DINAMIS (Ini inti request Anda)
                            // Hasil: donation-proofs/105-budi-santoso/
                            ->directory(function () {
                                $user = auth()->user();
                                // Pakai ID + Slug Nama agar unik & rapi (misal: 105-budi-santoso)
                                $folderName = $user->id . '-' . Str::slug($user->name);
                                return 'donation-proofs/' . $folderName;
                            })

                            // 3. RENAME FILE (Opsional tapi Recommended)
                            // Biar namanya gak aneh-aneh (misal: WhatsApp Image 2024....jpg jadi proof-17823.jpg)
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                return 'proof-' . now()->timestamp . '.' . $file->getClientOriginalExtension();
                            })

                            // ->required()
                            ->columnSpanFull(),
                            
                        Forms\Components\Section::make('Donor Message')
                            ->schema([
                                Forms\Components\Textarea::make('donor_message')
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable()->label('Date'),
                Tables\Columns\TextColumn::make('user.name')->searchable()->label('Donor'),
                Tables\Columns\TextColumn::make('program.title')->limit(20),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($record) => $record->currency . ' ' . number_format($record->amount)),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending_verification' => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_verification' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(), // Untuk ubah status
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
