<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MyIdCard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'My Digital ID';
    protected static string $view = 'filament.member.pages.my-id-card';

    public $user;
    public $cardTheme = 'default'; // Default design

    public function mount()
    {
        $this->user = Auth::user();
        
        // Redirect jika belum aktif
        if ($this->user->status !== 'active') {
            return redirect('/member');
        }
    }
}