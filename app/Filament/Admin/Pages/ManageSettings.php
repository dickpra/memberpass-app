<?php

namespace App\Filament\Admin\Pages;

use App\Models\GeneralSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'General Settings';
    protected static ?string $title = 'Application Settings';
    
    // File view blade yang akan dirender
    protected static string $view = 'filament.admin.pages.manage-settings';

    // Variabel untuk menampung data form
    public ?array $data = []; 
    
    // Saat halaman dibuka (Mount)
    public function mount(): void 
    {
        // Ambil data pertama, atau buat baru jika kosong
        $settings = GeneralSetting::firstOrNew();
        
        // Isi form dengan data dari database
        $this->form->fill($settings->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- TAB 1: Organization ---
                Forms\Components\Section::make('Organization Details')
                    ->schema([
                        Forms\Components\TextInput::make('organization_name')
                            ->required(),
                        Forms\Components\Textarea::make('organization_address')
                            ->rows(3),
                        Forms\Components\TextInput::make('vat_number')
                            ->label('Tax/VAT/NPWP Number'),
                    ])->columns(2),

                // --- TAB 2: Membership Pricing ---
                // Forms\Components\Section::make('Membership Pricing')
                //     ->description('Harga default untuk pendaftaran membership')
                //     ->schema([
                //         Forms\Components\TextInput::make('gold_price')
                //             ->label('Gold Price (Base)')
                //             ->numeric()
                //             ->prefix('IDR'),
                //         Forms\Components\TextInput::make('silver_price')
                //             ->label('Silver Price')
                //             ->numeric()
                //             ->prefix('IDR')
                //             ->required(),
                //         Forms\Components\TextInput::make('bronze_price')
                //             ->label('Bronze Price')
                //             ->numeric()
                //             ->prefix('IDR')
                //             ->required(),
                //     ])->columns(3),

                // --- TAB 3: Bank Information ---
                Forms\Components\Section::make('Bank Account Info')
                    ->description('Rekening tujuan transfer member')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->required(),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->required(),
                        Forms\Components\TextInput::make('bank_account_owner')
                            ->label('Account Holder Name')
                            ->required(),
                        Forms\Components\TextInput::make('bank_city'),
                        Forms\Components\TextInput::make('bank_swift_code')
                            ->label('SWIFT/BIC Code (International)'),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'IDR' => 'IDR (Indonesian Rupiah)',
                                'USD' => 'USD (US Dollar)',
                                'EUR' => 'EUR (Euro)',
                            ])
                            ->default('IDR')
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data'); // Sambungkan form ke variabel $data
    } 
    
    // Fungsi saat tombol Save ditekan
    public function save(): void
    {
        // Ambil data dari form
        $state = $this->form->getState();
        
        // Cari data ID 1 (Singleton)
        $settings = GeneralSetting::first();
        
        if ($settings) {
            $settings->update($state);
        } else {
            GeneralSetting::create($state);
        }
        
        // Tampilkan notifikasi sukses
        Notification::make() 
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}