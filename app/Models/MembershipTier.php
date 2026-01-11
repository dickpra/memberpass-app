<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipTier extends Model
{
    protected $fillable = [
        'name', 
        'price', 
        'benefits', 
        'css_class', 
        'is_active', 
        'is_invitation_only'
    ];

    protected $casts = [
        'benefits' => 'array', // PENTING: Agar JSON database terbaca sebagai Array PHP
        'is_active' => 'boolean',
        'is_invitation_only' => 'boolean',
    ];
}