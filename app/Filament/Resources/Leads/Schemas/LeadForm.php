<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadStatus;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company Info')
                    ->schema([
                        TextInput::make('company_name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('contact_person')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Contact Details')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->tel(),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        ToggleButtons::make('status')
                            ->options(LeadStatus::class)
                            ->inline()
                            ->default(LeadStatus::NEW)
                            ->required(),

                        TextInput::make('audit_report_url')
                            ->url()
                            ->label('Audit Report URL'),
                    ])->columns(2),
            ]);
    }
}
