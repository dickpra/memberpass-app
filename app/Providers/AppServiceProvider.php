<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail; // Import ini
use Illuminate\Notifications\Messages\MailMessage; // Import ini
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        // --- CUSTOM EMAIL VERIFIKASI (PAKAI VIEW SENDIRI) ---
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            
            return (new MailMessage)
                ->subject('Welcome to WFIED! Please Verify Your Email')
                ->view('emails.verification', [
                    'url' => $url,
                    'user' => $notifiable
                ]);
                
        });
        // ----------------------------------------------------
    }
}
