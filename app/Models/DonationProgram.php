<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DonationProgram extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    // Relasi: Satu program punya banyak donasi
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
    
    // Helper untuk menghitung total terkumpul (Hanya USD)
    public function getCollectedUsdAttribute()
    {
        return $this->donations()
            ->where('status', 'approved')
            ->where('currency', 'USD')
            ->sum('amount');
    }

    /**
     * THE CLEANER (TUKANG BERSIH-BERSIH)
     */
    protected static function booted(): void
    {
        // Event ini jalan kalau kita klik "Force Delete" (Hapus Permanen)
        static::forceDeleting(function (DonationProgram $program) {
            
            // PERBAIKAN DI SINI:
            // Hapus ->withTrashed() karena model Donation tidak pakai SoftDeletes.
            // Cukup ambil ->get() saja, dia akan ambil semua data.
            $histories = $program->donations()->get();

            foreach ($histories as $donation) {
                
                // A. Hapus File Bukti di Storage
                if ($donation->proof_file) {
                    // Pastikan pakai disk 'secure' sesuai setup terakhir
                    Storage::disk('secure')->delete($donation->proof_file);
                }

                // B. Hapus Data Donasi
                $donation->delete(); // Karena Donation tidak soft-delete, delete() ini sifatnya permanen
            }
        });
    }
}