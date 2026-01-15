<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;


class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. TOMBOL DOWNLOAD INVOICE (Selalu Ada)
            Actions\Action::make('download_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn ($record) => route('invoice.print', ['payment' => $record->id])),
                // ->openInNewTab()

            // 2. TOMBOL REJECT (PERMANEN)
            Actions\Action::make('reject_payment')
                ->label('REJECT')
                ->color('danger') // Merah
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Reject Payment Permanently?')
                ->modalDescription('Tindakan ini tidak bisa dibatalkan. User harus upload ulang dengan Invoice baru.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Alasan Penolakan (Wajib)')
                        ->required()
                        ->placeholder('Contoh: Bukti transfer buram / Nominal tidak sesuai.')
                ])
                // Hanya muncul jika status masih Waiting
                ->visible(fn ($record) => $record->status === 'waiting_verification')
                ->action(function ($record, array $data) {
                    // Update Payment
                    $record->update([
                        'status' => 'rejected',
                        'admin_note' => $data['reason']
                    ]);

                    // Update User jadi 'payment_rejected'
                    $record->user->update(['status' => 'payment_rejected']);

                    \Filament\Notifications\Notification::make()
                        ->title('Payment Rejected')
                        ->danger()
                        ->send();
                }),

            // 3. TOMBOL APPROVE (ACCEPT)
            Actions\Action::make('approve_payment')
                ->label('APPROVE')
                ->color('success') // Hijau
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Approve Payment?')
                ->modalDescription('Pastikan dana sudah masuk. Masa aktif member akan diset hingga 31 Desember.')
                // Hanya muncul jika status masih Waiting
                ->visible(fn ($record) => $record->status === 'waiting_verification')
                ->action(function ($record) {
                    
                    // 1. TENTUKAN TANGGAL EXPIRED (Logic 31 Desember)
                    // Kita cek tanggal pembuatan invoice (created_at), bukan tanggal approve admin hari ini
                    // agar adil jika admin telat approve.
                    $invoiceDate = $record->created_at;
                    
                    if ($invoiceDate->month == 12) {
                        // Jika bayar bulan 12 (Desember) -> Aktif sampai 31 Des Tahun DEPAN
                        $expiryDate = $invoiceDate->copy()->addYear()->endOfYear(); 
                    } else {
                        // Jika bayar bulan 1-11 -> Aktif sampai 31 Des Tahun INI
                        $expiryDate = $invoiceDate->copy()->endOfYear();
                    }

                    // 2. Update Status Payment
                    $record->update([
                        'status' => 'approved',
                        'admin_note' => 'Verified. Valid until ' . $expiryDate->format('d M Y')
                    ]);

                    // 3. Update Data Member
                    // 2. Update User Logic
                    $user = $record->user;
                    $user->status = 'active';
                    
                    // --- LOGIC ID MEMBER PERMANEN ---
                    // Cek dulu, apakah member_id KOSONG?
                    if (empty($user->member_id)) {
                        // Hanya generate jika belum punya ID sama sekali (Member Baru)
                        $user->member_id = \App\Models\User::generateMemberId(false); // False = Bukan VIP
                        $user->join_date = now();
                    }
                    // JIKA SUDAH ADA, JANGAN DIUBAH! (Skip logic generate)
                    
                    // Set Expired Date
                    $invoiceDate = $record->created_at;
                    if ($invoiceDate->month == 12) {
                        $user->expiry_date = $invoiceDate->copy()->addYear()->endOfYear();
                    } else {
                        $user->expiry_date = $invoiceDate->copy()->endOfYear();
                    }

                    $user->save();

                    \Filament\Notifications\Notification::make()
                        ->title('Payment Approved')
                        ->body("Member active until " . $expiryDate->format('d M Y'))
                        ->success()
                        ->send();
                }),
                
            // 4. TOMBOL EMERGENCY REVOKE (Hanya untuk Approved)
            // Kita izinkan revoke KHUSUS untuk Approved (jika admin salah klik Accept).
            // Tapi TIDAK ADA Revoke untuk Rejected (sesuai request kamu: permanen).
            // Actions\Action::make('approve_cancel')
            //         ->label('Approve Cancel')
            //         ->icon('heroicon-o-x-circle') // Icon silang bulat
            //         ->color('danger') // Warna merah
            //         ->requiresConfirmation()
            //         ->modalHeading('Setujui Pembatalan?')
            //         ->modalDescription('Tagihan ini akan dihapus dan User akan di-reset ke status Registered (bisa pilih paket ulang).')
            //         ->modalSubmitActionLabel('Ya, Batalkan')
                    
            //         // Hanya muncul jika status User adalah 'cancellation_requested'
            //         ->visible(fn (Payment $record) => $record->user && $record->user->status === 'cancellation_requested')
                    
            //         ->action(function (Payment $record) {
            //             $user = $record->user;
                        
            //             // 1. Reset Status User
            //             if ($user) {
            //                 $user->update([
            //                     'status' => 'registered',
            //                     'membership_type' => null, // Hapus pilihan paketnya
            //                 ]);
            //             }

            //             // 2. Hapus Data Payment ini (karena batal)
            //             $record->delete();

            //             // 3. Notifikasi
            //             \Filament\Notifications\Notification::make()
            //                 ->title('Payment cancelled & User reset.')
            //                 ->success()
            //                 ->send();
            //         }),
            Actions\Action::make('revoke_approval')
                ->label('Batal Approve')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'approved')
                ->action(function ($record) {
                    // Reset Payment
                    $record->update(['status' => 'waiting_verification']);
                    
                    // Reset User (Cek Logic Cerdas tadi)
                    $user = $record->user;
                    $hasOtherActivePayment = \App\Models\Payment::where('user_id', $user->id)
                        ->where('id', '!=', $record->id)
                        ->where('status', 'approved')
                        ->exists();

                    if (!$hasOtherActivePayment) {
                        $user->update(['status' => 'waiting_verification']);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Approval Revoked.')
                        ->warning()
                        ->send();
                }),
        ];
    }

    // --- LOGIKA UTAMA ADA DI SINI ---
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. Simpan dulu perubahan pada Payment (misal: update status jadi approved)
        $record->update($data);

        // 2. Cek apakah Status baru adalah 'approved'
        if ($record->status === 'approved') {
            
            // Ambil data User terkait
            $user = $record->user;

            // --- SKENARIO A: PENDAFTARAN BARU ---
            if ($record->type === 'registration') {
                
                // Aktifkan User
                $user->status = 'active';
                $user->join_date = now();
                $user->expiry_date = now()->addYear(); // 1 Tahun dari sekarang
                
                // Generate Member ID jika belum punya (Format: MEM-TAHUN-URUTAN)
                // Contoh: MEM-2024-00125
                if (empty($user->member_id)) {
                    $user->member_id = 'MEM-' . date('Y') . '-' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
                }
                
                $user->save();

                Notification::make()
                    ->title('User Activated!')
                    ->body("User {$user->name} is now ACTIVE with ID: {$user->member_id}")
                    ->success()
                    ->send();
            }

            // --- SKENARIO B: RENEWAL (PERPANJANGAN) ---
            elseif ($record->type === 'renewal') {
                
                // Tambah 1 tahun dari tanggal expired sebelumnya
                // Jika sudah expired lama, mulai dari hari ini lagi
                $currentExpiry = $user->expiry_date;
                
                if ($currentExpiry && $currentExpiry > now()) {
                    // Masih aktif, tambah 1 tahun dari expiry lama
                    $newExpiry = $currentExpiry->addYear();
                } else {
                    // Sudah mati, hidupkan 1 tahun dari hari ini
                    $newExpiry = now()->addYear();
                }

                $user->expiry_date = $newExpiry;
                $user->status = 'active'; // Pastikan aktif kalau sebelumnya expired
                $user->save();

                Notification::make()
                    ->title('Membership Extended!')
                    ->body("User {$user->name} extended until {$newExpiry->format('d M Y')}")
                    ->success()
                    ->send();
            }
        }
        
        // 3. Jika status REJECTED
        elseif ($record->status === 'rejected') {
            $user = $record->user;
            
            // UPDATE STATUS USER JADI 'payment_rejected'
            // Ini status baru biar kita bisa kasih alert merah di dashboard
            $user->status = 'payment_rejected';
            $user->save();
        }

        return $record;
    }
}