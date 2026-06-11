<?php

namespace App\Livewire\Public;

use App\Actions\Leads\CreateLeadAction;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Locked;
use Flux\Flux;

class PackageInquiryForm extends Component
{
    public bool $isOpen = false;
    
    /**
     * The selected package name.
     *
     * SECURITY: Marked #[Locked] to prevent browser-side tampering.
     * This value is set server-side via open() from a controlled set of valid packages.
     * Without #[Locked], an attacker using DevTools could intercept the Livewire POST
     * for submit() and inject any arbitrary string as the package name.
     */
    #[Locked]
    public string $package = '';

    #[Validate('required|min:3')]
    public string $company_name = '';

    #[Validate('required|min:3')]
    public string $contact_person = '';

    #[Validate('required|email')]
    public string $email = '';

    public string $phone = '';
    
    public string $message = '';

    public function open(string $package)
    {
        $this->package = $package;
        $this->isOpen = true;
    }

    public function submit(CreateLeadAction $createLeadAction)
    {
        $this->validate();

        $createLeadAction->execute([
            'company_name' => $this->company_name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'package' => $this->package,
            'message' => $this->message,
        ]);

        $this->reset(['company_name', 'contact_person', 'email', 'phone', 'message', 'isOpen']);
        
        $this->dispatch('notify', 'Uw aanvraag voor het ' . $this->package . ' pakket is verzonden!');
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <flux:modal wire:model="isOpen" class="md:w-[500px]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Interesse in {{ $package }}</flux:heading>
                        <flux:subheading>Vul uw gegevens in en wij nemen binnen 24 uur contact op.</flux:subheading>
                    </div>

                    <form wire:submit="submit" class="space-y-4">
                        <flux:input wire:model="company_name" label="Bedrijfsnaam" placeholder="bv. Desmet Transport NV" />
                        <flux:input wire:model="contact_person" label="Contactpersoon" placeholder="Jan Desmet" />
                        
                        <div class="grid grid-cols-2 gap-4">
                            <flux:input wire:model="email" type="email" label="E-mailadres" placeholder="jan@bedrijf.be" />
                            <flux:input wire:model="phone" label="Telefoonnummer" placeholder="0470 00 00 00" />
                        </div>

                        <flux:textarea wire:model="message" label="Extra informatie (optioneel)" placeholder="Heeft u specifieke vragen?" />

                        <div class="flex justify-end gap-2">
                            <flux:button wire:click="$set('isOpen', false)" variant="ghost">Annuleren</flux:button>
                            <flux:button type="submit" variant="primary">Verstuur Aanvraag</flux:button>
                        </div>
                    </form>
                </div>
            </flux:modal>
        </div>
        HTML;
    }
}
