<?php

namespace App\Filament\Member\Pages;

use App\Models\Donation;
use App\Models\DonationPaymentMethod;
use App\Models\DonationProgram;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile; // Import iniuse Illuminate\Support\Str;


class Donations extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Financial';
    protected static ?string $title = 'Donations';
    protected static string $view = 'filament.member.pages.donations';

    // --- STATE MANAGEMENT ---
    public $activeTab = 'campaigns'; // 'campaigns' or 'history'
    public $selectedProgram = null;  // Program object
    
    // Form Data
    public ?array $data = [];

    public function resetProgram()
    {
        $this->selectedProgram = null;
        $this->form->fill(); // Kosongkan form input
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    // 1. FORM SCHEMA (Untuk Upload Bukti)
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transfer Confirmation')
                    ->description('Fill in this form after you have made the transfer.')
                    ->schema([
                        Forms\Components\Hidden::make('donation_program_id'), // Diset otomatis nanti

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Donation Amount')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('Example: 50 or 500000'),

                                Forms\Components\Select::make('currency')
                                    ->label('Currency')
                                    ->options([
                                        // 'IDR' => 'IDR (Rupiah)',
                                        'USD' => 'USD (Dollar)',
                                    ])
                                    ->required()
                                    ->default('USD'),
                            ]),

                        Forms\Components\Select::make('donation_payment_method_id')
                            ->label('Transfer Method')
                            ->options(DonationPaymentMethod::where('is_active', true)->pluck('provider_name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('sender_name')
                            ->label('Sender Name (As on Bank Account/PayPal)')
                            ->required()
                            ->placeholder('Name on bank transfer'),

                        Forms\Components\FileUpload::make('proof_file')
                            ->label('Upload Transfer Proof')
                            ->image()
                            
                            // 1. DISK SECURE (Wajib)
                            ->disk('secure')
                            ->visibility('private')

                            // 2. FOLDER DINAMIS (Ini inti request Anda)
                            // Hasil: donation-proofs/105-budi-santoso/
                            ->directory(function () {
                                $user = auth()->user();
                                // Pakai ID + Slug Nama agar unik & rapi (misal: 105-budi-santoso)
                                $folderName = $user->id . '-' . Str::slug($user->name);
                                return 'donation-proofs/' . $folderName;
                            })

                            // 3. RENAME FILE (Opsional tapi Recommended)
                            // Biar namanya gak aneh-aneh (misal: WhatsApp Image 2024....jpg jadi proof-17823.jpg)
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                return 'proof-' . now()->timestamp . '.' . $file->getClientOriginalExtension();
                            })

                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('donor_message')
                            ->label('Message / Prayer (Optional)')
                            ->placeholder('Write your support message...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
            ])->statePath('data');
    }

    // 2. ACTION: Saat Member klik "Donate Now" di kartu program
    public function selectProgram($programId)
    {
        $this->selectedProgram = DonationProgram::find($programId);
        
        // Reset form & isi ID program
        $this->form->fill([
            'donation_program_id' => $programId,
            'currency' => 'USD', // Default
        ]);

        // Pindah scroll ke form (opsional, ditangani di blade via x-data)
    }

    // 3. ACTION: Submit Form
    public function create()
    {
        $state = $this->form->getState();

        Donation::create([
            'user_id' => Auth::id(),
            'donation_program_id' => $state['donation_program_id'],
            'donation_payment_method_id' => $state['donation_payment_method_id'],
            'amount' => $state['amount'],
            'currency' => $state['currency'],
            'sender_name' => $state['sender_name'],
            'proof_file' => $state['proof_file'],
            'donor_message' => $state['donor_message'],
            'status' => 'pending_verification',
        ]);

        Notification::make()
            ->title('Donation Successfully Submitted')
            ->body('Thank you! We will verify your transfer proof.')
            ->success()
            ->send();

        // Reset
        $this->selectedProgram = null;
        $this->form->fill();
        $this->activeTab = 'history'; // Switch to history tab so member can see the status
    }

    // 4. DATA LOADER (Untuk View)
   protected function getViewData(): array
    {
        return [
            // Untuk List Donasi Aktif: Tetap ambil yang aktif saja
            'programs' => DonationProgram::where('is_active', true)->get(),
            
            'paymentMethods' => DonationPaymentMethod::where('is_active', true)->get(),
            
            // UNTUK HISTORY: Gunakan withTrashed()
            // Artinya: "Ambil data donasi beserta data programnya, 
            // MESKIPUN programnya sudah dihapus (di tong sampah)"
            'myDonations' => Donation::with(['program' => function ($query) {
                    $query->withTrashed(); 
                }])
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),
        ];
    }
}