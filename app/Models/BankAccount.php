<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    // Izinkan kolom-kolom ini diisi
    protected $fillable = [
        'bank_name',        // BCA, Mandiri
        'account_number',   // 123456
        'account_owner',    // PT WFIED
        'bank_city',        // Jakarta Branch (Optional)
        'swift_code',       // CENAIDJA (Optional - International)
        'logo',             // Gambar Logo
        'is_active',        // Status On/Off
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}