<?php

namespace App\Filament\Member\Pages;

use App\Models\GeneralSetting;
use App\Models\Payment;
use Filament\Actions\Action; // Import Action
use Filament\Actions\Concerns\InteractsWithActions; // Import InteractsWithActions
use Filament\Actions\Contracts\HasActions; // Import HasActions
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\MembershipTier;
use Carbon\Carbon;



class Dashboard extends BaseDashboard implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions; // Pakai Trait ini

    protected static string $view = 'filament.member.pages.dashboard';
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user->status === 'waiting_payment') {
            $payment = Payment::where('user_id', $user->id)
                ->where('status', 'waiting_verification')
                ->latest()
                ->first();

            if ($payment) {
                $this->form->fill([
                    'amount' => $payment->amount,
                    'sender_name' => $user->name,
                ]);
            }
        }
    }

    // // --- DEFINISI ACTION PILIH PAKET (DENGAN KONFIRMASI) ---
    // public function selectSilverAction(): Action
    // {
    //     return Action::make('selectSilver')
    //         ->label('Choose Silver')
    //         ->color('gray')
    //         ->extraAttributes(['class' => 'w-full']) // Agar tombol full width
    //         ->requiresConfirmation() // MEMUNCULKAN MODAL KONFIRMASI
    //         ->modalHeading('Konfirmasi Pilihan Silver')
    //         ->modalDescription('Apakah Anda yakin ingin memilih paket Silver? Tagihan akan dibuat setelah ini.')
    //         ->modalSubmitActionLabel('Ya, Saya Yakin')
    //         ->action(fn () => $this->processSelection('Silver'));
    // }

    // public function selectBronzeAction(): Action
    // {
    //     return Action::make('selectBronze')
    //         ->label('Choose Bronze')
    //         ->color('warning')
    //         ->extraAttributes(['class' => 'w-full'])
    //         ->requiresConfirmation()
    //         ->modalHeading('Konfirmasi Pilihan Bronze')
    //         ->modalDescription('Apakah Anda yakin ingin memilih paket Bronze?')
    //         ->modalSubmitActionLabel('Ya, Lanjut Bayar')
    //         ->action(fn () => $this->processSelection('Bronze'));
    // }

    // public function selectGoldAction(): Action
    // {
    //     return Action::make('selectGold')
    //         ->label('Choose Gold')
    //         ->color('warning')
    //         ->extraAttributes(['class' => 'w-full'])
    //         ->requiresConfirmation()
    //         ->modalHeading('Request Gold Membership')
    //         ->modalDescription('Gold Membership membutuhkan persetujuan Admin atau harga khusus. Lanjut?')
    //         ->modalSubmitActionLabel('Kirim Request')
    //         ->action(fn () => $this->processSelection('Gold'));
    // }

    public function selectTierAction(): Action
    {
        return Action::make('selectTier')
            ->label('Select This Plan')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => 'Select ' . $arguments['name'] . '?') // Judul Modal Dinamis
            ->modalDescription('Tagihan akan dibuat sesuai harga paket.')
            ->action(function (array $arguments) {
                $this->processSelection($arguments['id']);
            });
    }

    // --- LOGIKA UTAMA PEMILIHAN ---
    // Tambahkan method ini untuk Print Invoice
    public function downloadInvoiceAction(): Action
    {
        return Action::make('downloadInvoice')
            ->label('Download / Print Invoice')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->url(fn () => route('invoice.print', ['payment' => Payment::where('user_id', Auth::id())->latest()->first()->id ?? 0]))
            ->openUrlInNewTab();
    }

    // UPDATE LOGIKA: processSelection
    // 2. Update processSelection agar menerima ID, bukan String
    public function processSelection($tierId)
    {
        $user = Auth::user();
        $settings = GeneralSetting::first();
        $tier = MembershipTier::find($tierId);

        // SECURITY CHECK:
        // Jika user mencoba memilih Tier yang 'Invitation Only' (VIP) lewat inspect element,
        // Kita tolak mentah-mentah.
        if ($tier->is_invitation_only) {
            Notification::make()
                ->title('Action Unauthorized')
                ->body('This tier is Invitation Only (Admin Promote).')
                ->danger()
                ->send();
            return;
        }

        // ==========================================
        // LOGIC PRORATED PRICING & ROLLOVER
        // ==========================================
        
        $now = Carbon::now();
        $currentYear = $now->year;
        $basePrice = $tier->price;
        $finalPrice = 0;
        $note = '';

        // ATURAN 1: BULAN DESEMBER (Rollover ke Tahun Depan)
        if ($now->month == 12) {
            // Bayar Full, tapi aktif sampai tahun depan
            $finalPrice = $basePrice;
            $note = "Early Bird {$currentYear} (Bonus Des + Full " . ($currentYear + 1) . ")";
        } 
        // ATURAN 2: JANUARI - NOVEMBER (Prorated)
        else {
            // Hitung total hari dalam tahun ini (365 atau 366)
            $daysInYear = $now->copy()->endOfYear()->dayOfYear; 
            
            // Hitung sisa hari dari hari ini sampai 31 Des
            // diffInDays menghasilkan selisih, tambah 1 biar hari ini terhitung
            $remainingDays = $now->diffInDays($now->copy()->endOfYear()) + 1;

            // Rumus Prorate: (Sisa Hari / Total Hari) * Harga Dasar
            $calculatedPrice = ($remainingDays / $daysInYear) * $basePrice;

            // Cek Minimal Pembayaran (Setara 1 Bulan)
            $minPrice = $basePrice / 12;

            if ($calculatedPrice < $minPrice) {
                $finalPrice = $minPrice;
                $note = "Prorated (Minimum 1 Month Rule)";
            } else {
                // Pembulatan ke ribuan terdekat (biar angkanya cantik, misal 123.456 jadi 124.000)
                $finalPrice = ceil($calculatedPrice / 1000) * 1000;
                $note = "Prorated for {$remainingDays} days remaining";
            }
        }

        // ==========================================
        
        // Simpan data ke User & Payment
        $user->update(['membership_type' => $tier->name, 'status' => 'waiting_payment']);

        Payment::create([
            'user_id' => $user->id,
            'amount' => $finalPrice, // Harga hasil kalkulasi
            'currency' => $settings->currency ?? 'IDR',
            'type' => 'registration',
            'status' => 'pending_upload',
            'sender_name' => $user->name,
            // Simpan catatan hitungan agar admin & user paham kenapa harganya segitu
            'admin_note' => $note, 
        ]);

        // Tampilkan notifikasi dengan info harga
        Notification::make() 
            ->title('Invoice Created')
            ->body("Total: IDR " . number_format($finalPrice) . " ({$note})")
            ->success()
            ->send();

        return redirect()->to('/member');
    }

    // UPDATE LOGIKA: submitPayment (Saat Upload Bukti)
    public function submitPayment()
    {
        $user = Auth::user();
        $data = $this->form->getState();

        // Cari payment yang statusnya masih pending_upload (atau waiting_verification jika re-upload)
        $payment = Payment::where('user_id', $user->id)
            ->whereIn('status', ['pending_upload', 'waiting_verification'])
            ->latest()
            ->first();

        if ($payment) {
            $payment->update([
                'sender_name' => $data['sender_name'],
                // PERUBAHAN PENTING: Saat submit, baru ubah jadi 'waiting_verification'
                // AGAR MUNCUL DI ADMIN
                'status' => 'waiting_verification', 
            ]);

            foreach ($data['payment_proofs'] as $filePath) {
                $payment->files()->create([
                    'file_path' => $filePath,
                    'file_type' => 'image',
                ]);
            }

            $user->update(['status' => 'waiting_verification']);
            
            Notification::make()->title('Proof submitted! Sent to Admin.')->success()->send();
            return redirect()->to('/member');
        }
    }

    // UPDATE LOGIKA: Cancel Order (Smart Cancel)
    // Jika belum upload bukti, langsung hapus saja (tidak perlu acc admin).
    // Jika sudah upload, baru minta acc admin.
    public function cancelOrderAction(): Action
    {
        return Action::make('cancelOrder')
            ->label('Batalkan Pesanan')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Pesanan?')
            ->action(function () {
                $user = Auth::user();
                $payment = Payment::where('user_id', $user->id)->latest()->first();
                
                // Skenario 1: Belum upload bukti (Admin belum lihat) -> Hapus Langsung
                if ($payment && $payment->status === 'pending_upload') {
                    $payment->delete();
                    $user->update(['status' => 'registered', 'membership_type' => null]);
                    Notification::make()->title('Order cancelled.')->success()->send();
                    return redirect()->to('/member');
                }
                
                // Skenario 2: Sudah upload bukti (Admin sudah lihat) -> Minta Izin
                if ($payment && $payment->status === 'waiting_verification') {
                    $user->update(['status' => 'cancellation_requested']);
                    Notification::make()->title('Cancellation requested to Admin.')->success()->send();
                    return redirect()->to('/member');
                }
            });
    }

    // --- FORM UPLOAD ---
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Konfirmasi Pembayaran')
                    ->schema([
                        // 1. AMOUNT OTOMATIS (Read Only)
                        // TextInput::make('amount')
                        //     ->label('Nominal Transfer (IDR)')
                        //     // ->disabled() // User tidak bisa edit
                        //     // ->dehydrated(false) // Data ini tidak ditimpa ke database (aman)
                        //     ->required(),
                            
                        TextInput::make('sender_name')
                            ->label('Nama Pengirim di Rekening')
                            ->placeholder('Contoh: Budi Santoso')
                            ->required(),

                        // 2. STORAGE PER FOLDER NAMA MEMBER & INVOICE
                        FileUpload::make('payment_proofs')
                            ->label('Bukti Transfer')
                            ->disk('public')
                            // LOGIKA FOLDER DINAMIS
                            ->directory(function () {
                                $user = Auth::user();
                                
                                // Ambil Invoice yang sedang aktif
                                $payment = Payment::where('user_id', $user->id)
                                    ->whereIn('status', ['pending_upload', 'waiting_verification', 'payment_rejected'])
                                    ->latest()
                                    ->first();
                                    
                                // Buat slug nama user (misal: "Budi Santoso" -> "budi-santoso")
                                $safeName = Str::slug($user->name);
                                // Ambil ID Invoice
                                $invoiceId = $payment ? $payment->id : 'temp';

                                // Hasil: payment-proofs/budi-santoso/inv-105
                                return "payment-proofs/{$safeName}/inv-{$invoiceId}";
                            })
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->required(),
                    ])
            ])->statePath('data');
    }

    // 1. Tambahkan Action Try Again
    public function tryAgainAction(): Action
    {
        return Action::make('tryAgain')
            ->label('Upload Ulang Bukti')
            ->color('danger') // Tombol Merah
            ->icon('heroicon-o-arrow-path')
            ->requiresConfirmation()
            ->modalHeading('Upload Ulang Pembayaran')
            ->modalDescription('Anda akan diarahkan kembali ke form upload. Pastikan bukti baru sudah benar.')
            ->action(function () {
                $user = Auth::user();
                $settings = GeneralSetting::first();
                
                // Ambil data payment terakhir yang ditolak untuk dicopy nominalnya
                $lastPayment = Payment::where('user_id', $user->id)->latest()->first();

                // Buat Payment Baru (Draft/Pending)
                Payment::create([
                    'user_id' => $user->id,
                    'amount' => $lastPayment ? $lastPayment->amount : 0, // Pakai nominal lama
                    'currency' => $settings->currency ?? 'IDR',
                    'type' => 'registration',
                    'status' => 'pending_upload', // Reset ke pending upload
                    'sender_name' => $user->name,
                ]);

                // Kembalikan status user ke waiting_payment (Mode Upload)
                $user->update(['status' => 'waiting_payment']);

                Notification::make()->title('Silakan upload bukti baru.')->success()->send();
                return redirect()->to('/member');
            });
    }

    
}