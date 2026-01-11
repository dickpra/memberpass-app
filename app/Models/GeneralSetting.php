<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'organization_name',
    //     'organization_address',
    //     'vat_number',
    //     'bank_name',
    //     'bank_account_number',
    //     'bank_account_owner',
    //     'bank_city',
    //     'bank_swift_code',
    //     'currency',
    // ];

    protected $guarded = [];
}