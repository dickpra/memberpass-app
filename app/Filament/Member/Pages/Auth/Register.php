<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    // Override method form untuk menambah field
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Bawaan: Name
                $this->getNameFormComponent(),
                
                // Bawaan: Email
                $this->getEmailFormComponent(),
                
                // --- CUSTOM FIELDS KITA ---
                TextInput::make('phone')
                    ->label('WhatsApp / Phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                Select::make('country')
                    ->label('Country')
                    ->options(
                        collect(config('countries'))
                            ->mapWithKeys(fn ($country) => [$country => $country])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('organization')
                    ->label('Organization / Company')
                    ->placeholder('Optional')
                    ->maxLength(255),
                // --------------------------

                // Bawaan: Password
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])
            ->statePath('data');
    }
}