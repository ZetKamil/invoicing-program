<?php

namespace App\Livewire\Public;

use App\Actions\Leads\CreateLeadAction;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ContactForm extends Component
{
    #[Validate('required|min:2')]
    public string $first_name = '';

    #[Validate('required|min:2')]
    public string $last_name = '';

    #[Validate('required|min:3')]
    public string $company_name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $package = '';

    public string $message = '';

    public bool $success = false;

    /**
     * Handle the form submission.
     */
    public function submit(CreateLeadAction $createLeadAction)
    {
        $this->validate();

        $createLeadAction->execute([
            'company_name' => $this->company_name,
            'contact_person' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'package' => $this->package,
            'message' => $this->message,
        ]);

        $this->success = true;
        $this->reset(['first_name', 'last_name', 'company_name', 'email', 'package', 'message']);
        
        $this->dispatch('notify', 'Uw bericht is succesvol verzonden!');
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @if ($success)
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: #4ade80; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 20px;">
                    <h4 style="font-family: 'Syne', sans-serif; margin-bottom: 8px;">Bericht Verzonden!</h4>
                    <p style="font-size: 14px;">Bedankt voor uw interesse. We nemen binnen 24 uur contact met u op.</p>
                </div>
            @endif

            <form wire:submit="submit" class="contact-form">
                <div class="cf-row">
                  <div class="cf-group">
                    <label>Voornaam</label>
                    <input type="text" wire:model="first_name" placeholder="Jan" />
                    @error('first_name') <span style="color: var(--orange); font-size: 11px;">{{ $message }}</span> @enderror
                  </div>
                  <div class="cf-group">
                    <label>Achternaam</label>
                    <input type="text" wire:model="last_name" placeholder="Desmet" />
                    @error('last_name') <span style="color: var(--orange); font-size: 11px;">{{ $message }}</span> @enderror
                  </div>
                </div>
                <div class="cf-group">
                  <label>Bedrijfsnaam</label>
                  <input type="text" wire:model="company_name" placeholder="Desmet Transport NV" />
                  @error('company_name') <span style="color: var(--orange); font-size: 11px;">{{ $message }}</span> @enderror
                </div>
                <div class="cf-group">
                  <label>E-mailadres</label>
                  <input type="email" wire:model="email" placeholder="jan@desmettransport.be" />
                  @error('email') <span style="color: var(--orange); font-size: 11px;">{{ $message }}</span> @enderror
                </div>
                <div class="cf-group">
                  <label>Pakket interesse</label>
                  <select wire:model="package">
                    <option value="">Selecteer een pakket...</option>
                    <option value="Start">Start — €850</option>
                    <option value="Pro Transport">Pro Transport — €1.150</option>
                    <option value="Enterprise">Enterprise — €1.950+</option>
                    <option value="Audit">Eerst gratis audit aanvragen</option>
                  </select>
                  @error('package') <span style="color: var(--orange); font-size: 11px;">{{ $message }}</span> @enderror
                </div>
                <div class="cf-group">
                  <label>Bericht</label>
                  <textarea wire:model="message" rows="4" placeholder="Vertel ons kort over uw huidige situatie en wat u zoekt..."></textarea>
                </div>
                <button type="submit" class="btn-primary" style="justify-content:center" wire:loading.attr="disabled">
                    <span wire:loading.remove>Verstuur bericht →</span>
                    <span wire:loading>Verzenden...</span>
                </button>
            </form>
        </div>
        HTML;
    }
}
