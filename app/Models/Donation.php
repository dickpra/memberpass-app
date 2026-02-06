<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(DonationProgram::class, 'donation_program_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(DonationPaymentMethod::class, 'donation_payment_method_id');
    }
}