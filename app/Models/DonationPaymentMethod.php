<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationPaymentMethod extends Model
{
    protected $guarded = [];

    // Nanti di Admin form, kita kasih pilihan type
    public static function getTypes()
    {
        return [
            'paypal' => 'PayPal (Global)',
            'bank_transfer' => 'Bank Transfer (Local/Swift)',
            // Bisa ditambah: 'crypto', 'stripe', dll
        ];
    }
}