<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Implement FilamentUser agar bisa login ke panel
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Data yang boleh diisi (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'country',
        'organization',
        'membership_type',
        'member_id',
        'join_date',
        'expiry_date',
        'status',
        'role',
    ];

    /**
     * Konversi tipe data otomatis
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'join_date' => 'date',   // Agar jadi objek Date di kodingan
        'expiry_date' => 'date', // Agar jadi objek Date di kodingan
    ];

    /**
     * LOGIKA LOGIN PANEL (PENTING!)
     * Membedakan siapa yang boleh masuk Admin Panel vs Member Panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            // Hanya role 'admin' yang bisa masuk panel Admin
            return $this->role === 'admin';
        }

        if ($panel->getId() === 'member') {
            // Semua user (admin/member) bisa masuk panel Member,
            // tapi idealnya hanya 'member' atau statusnya aktif.
            // Untuk sekarang kita set true agar user bisa login.
            return true; 
        }

        return false;
    }

    /**
     * RELASI: User punya banyak Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Tambahkan di paling bawah class User
    public static function generateMemberId($isVip = false)
    {
        // Tentukan Prefix
        $prefix = 'WFIED';
        $year = date('y'); // 2 digit tahun (misal: 26)

        do {
            // Generate 5 digit angka acak
            $random = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Hasil: VIP-WFIED-26-12345 atau WFIED-26-12345
            $newId = "{$prefix}-{$year}{$random}";

            // Cek duplikat di database
        } while (self::where('member_id', $newId)->exists());

        return $newId;
    }
}