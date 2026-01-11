<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'sender_name',
        'sender_bank',
        'type',           // registration / renewal
        'status',         // waiting_verification / approved / rejected
        'admin_note',
        'verified_by',
    ];

    // Relasi: Pembayaran milik satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Pembayaran punya banyak File Bukti
    public function files()
    {
        return $this->hasMany(PaymentFile::class);
    }
    
    // Relasi: Siapa admin yang verifikasi
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}