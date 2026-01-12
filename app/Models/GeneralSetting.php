<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    // protected $fillable = [
    //     // 1. CMS / Public
    //     'site_title',
    //     'site_description',
    //     'site_logo',
    //     'footer_text',
        
    //     // 2. Organization / Legal (Invoice Data)
    //     'organization_name',
    //     'organization_address',
    //     'tax_number',
        
    //     // 3. Announcement
    //     'announcement_active',
    //     'announcement_text',
        
    //     // 4. Support
    //     'support_phone',
    //     'support_email',
        
    //     // 5. System
    //     'currency',
    // ];
    
    protected $guarded = [];

    protected $casts = [
        'announcement_active' => 'boolean',
    ];
}