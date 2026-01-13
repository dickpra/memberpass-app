<?php

namespace App\Filament\Member\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Edit Profile';
    protected static string $view = 'filament.member.pages.my-profile';

    public ?array $data = [];

    public function mount(): void
    {
        // Isi form dengan data user saat ini
        $this->form->fill(auth()->user()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    // ->description('Foto ini akan digunakan pada ID Card Digital Anda.')
                    ->schema([
                        // UPLOAD FOTO PROFIL
                        // Forms\Components\FileUpload::make('avatar_url') // Pastikan ada kolom ini di DB atau pakai accessor
                        //     ->label('Foto Profil (Wajib Formal)')
                        //     ->avatar() // Mode bundar
                        //     ->image()
                        //     ->imageEditor()
                        //     ->directory('avatars')
                        //     ->rules(['nullable', 'image', 'max:1024']), // Max 1MB

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->disabled() // Email tidak boleh ganti sembarangan (identitas akun)
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->label('WhatsApp Number'),
                        
                        Forms\Components\TextInput::make('organization')
                            ->label('Organization / Company'),
                    ])->columns(2),

                Forms\Components\Section::make('Keamanan')
                    ->schema([
                        Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->rule(\Illuminate\Validation\Rules\Password::default()),
                            
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->same('new_password'),
                    ])->collapsed(), // Default tertutup biar rapi
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // 1. Update Password jika diisi
        if (!empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        // 2. Update Data Diri
        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        $user->organization = $data['organization'] ?? null;
        
        // Simpan Path Gambar (Jika di database kolomnya 'avatar_url' atau buat kolom baru 'avatar')
        // Asumsi kita pakai kolom bawaan filament user yg biasanya perlu ditambahkan
        // Mari kita simpan di kolom 'avatar_url' (Pastikan sudah add di migration users table)
        // Jika belum ada, jalankan migration: php artisan make:migration add_avatar_to_users
        if (isset($data['avatar_url'])) {
             $user->avatar_url = $data['avatar_url'];
        }

        $user->save();

        Notification::make()
            ->title('Profile Updated')
            ->success()
            ->send();
            
        // Reset field password
        $this->data['new_password'] = null;
        $this->data['new_password_confirmation'] = null;
    }
}