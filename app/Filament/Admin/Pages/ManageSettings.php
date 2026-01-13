<?php

namespace App\Filament\Admin\Pages;

use App\Models\GeneralSetting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'General Settings';
    protected static ?string $navigationGroup = 'Settings Management';

    protected static ?string $title = 'Website & Payment Settings';
    
    protected static string $view = 'filament.admin.pages.manage-settings';

    // 1. WAJIB: Variabel penampung data form
    public ?array $data = []; 

    public function mount(): void
    {
        // Ambil data setting pertama, atau buat baru jika kosong
        $settings = GeneralSetting::firstOrNew();
        
        // Isi form dengan data yang ada di database
        $this->form->fill($settings->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        
                        // TAB 1: IDENTITAS PERUSAHAAN (PENTING BUAT INVOICE)
                    \Filament\Forms\Components\Tabs\Tab::make('Legal & Invoice')
                        ->icon('heroicon-o-building-office-2')
                        ->schema([
                            \Filament\Forms\Components\Section::make('Identitas Organisasi')
                                ->description('Data ini akan muncul di Header Invoice dan Info Transfer Member.')
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('organization_name')
                                        ->label('Organization / Company Name')
                                        ->placeholder('Contoh: PT WFIED INDONESIA')
                                        ->required()
                                        ->columnSpanFull(), // Lebar penuh biar panjang muat
                                    
                                    \Filament\Forms\Components\TextInput::make('tax_number')
                                        ->label('Tax ID / NPWP (Opsional)')
                                        ->placeholder('Contoh: 12.345.678.9-012.000'),

                                    \Filament\Forms\Components\Textarea::make('organization_address')
                                        ->label('Alamat Lengkap')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // TAB 2: TAMPILAN WEBSITE (CMS)
                    \Filament\Forms\Components\Tabs\Tab::make('Website / CMS')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('site_title')
                                ->label('Nama Aplikasi')
                                ->default('WFIED Membership')
                                ->required(),

                            \Filament\Forms\Components\FileUpload::make('site_logo')
                                ->label('Logo Aplikasi')
                                ->image()
                                ->directory('settings') // Disimpan di storage/app/public/settings
                                ->imageEditor(),

                            \Filament\Forms\Components\Textarea::make('site_description')
                                ->label('Meta Description')
                                ->rows(2),

                            \Filament\Forms\Components\TextInput::make('footer_text')
                                ->label('Teks Footer')
                                ->placeholder('Copyright © 2026 WFIED'),
                        ]),

                    // // TAB 3: PENGUMUMAN DASHBOARD
                    // \Filament\Forms\Components\Tabs\Tab::make('Member Dashboard')
                    //     ->icon('heroicon-o-megaphone')
                    //     ->schema([
                    //         \Filament\Forms\Components\Section::make('Announcement Bar')
                    //             ->schema([
                    //                 \Filament\Forms\Components\Toggle::make('announcement_active')
                    //                     ->label('Aktifkan Pengumuman')
                    //                     ->helperText('Jika aktif, kotak biru berisi pesan akan muncul di dashboard member.'),
                                    
                    //                 \Filament\Forms\Components\Textarea::make('announcement_text')
                    //                     ->label('Isi Pesan')
                    //                     ->rows(3)
                    //                     ->placeholder('Contoh: Sistem sedang maintenance pada hari Sabtu...'),
                    //             ]),
                    //     ]),

                    // TAB 4: KONTAK SUPPORT
                    \Filament\Forms\Components\Tabs\Tab::make('Support Contact')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('support_phone')
                                ->label('WhatsApp Admin')
                                ->prefix('+62')
                                ->placeholder('812345678')
                                ->helperText('Masukkan nomor tanpa angka 0 di depan.'),

                            \Filament\Forms\Components\TextInput::make('support_email')
                                ->label('Email Support')
                                ->email(),
                        ]),
                    
                    // TAB 5: SYSTEM
                    // \Filament\Forms\Components\Tabs\Tab::make('System')
                    //     ->icon('heroicon-o-cog')
                    //     ->schema([
                    //         \Filament\Forms\Components\TextInput::make('currency')
                    //             ->label('Mata Uang Default')
                    //             ->default('IDR')
                    //             ->readOnly(),
                    //     ]),
                ])
                ->columnSpanFull()
            ])
            // 2. WAJIB: Hubungkan form ke variabel $data
            ->statePath('data'); 
    }

    // 3. Action Save
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        // 1. Ambil data dari form
        $state = $this->form->getState();

        // --- FIX BUG: "Column cannot be null" ---
        // Kita paksa konversi ke Boolean. 
        // Jika null/kosong, dia akan otomatis jadi false (0).
        $state['announcement_active'] = (bool) ($state['announcement_active'] ?? false);

        // 2. Simpan ke Database
        $settings = GeneralSetting::first();
        if ($settings) {
            $settings->update($state);
        } else {
            GeneralSetting::create($state);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}