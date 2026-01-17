<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;


class Register extends BaseRegister
{
    // Override method form untuk menambah field
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Bawaan: Name
                $this->getNameFormComponent(),
                
                // Bawaan: Email
                $this->getEmailFormComponent(),
                
                // --- CUSTOM FIELDS KITA ---
                TextInput::make('phone')
                    ->label('WhatsApp / Phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                Select::make('country')
                    ->label('Country')
                    ->options(
                        collect(config('countries'))
                            ->mapWithKeys(fn ($country) => [$country => $country])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('organization')
                    ->label('Organization / Company')
                    ->placeholder('Optional')
                    ->maxLength(255),
                // --------------------------

                // Bawaan: Password
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])
            ->statePath('data');
    }

    // Kita override fungsi utama pendaftaran
    protected function handleRegistration(array $data): Model
    {
        // 1. Jalankan proses pendaftaran standar (simpan user ke DB)
        $user = parent::handleRegistration($data);

        // 2. TAMPILKAN NOTIFIKASI SUKSES
        Notification::make()
            ->title('Pendaftaran Berhasil!')
            ->body('Akun Anda telah dibuat. Silakan periksa kotak masuk (atau spam) email Anda untuk verifikasi.')
            ->success() // Warna hijau
            ->duration(10000) // Tampil agak lama (10 detik) biar terbaca
            ->send();

        // 3. Kembalikan object user agar proses redirect berjalan lanjut
        return $user;
    }
}