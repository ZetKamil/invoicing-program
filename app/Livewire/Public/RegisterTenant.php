<?php

namespace App\Livewire\Public;

use App\Services\StripeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')]
class RegisterTenant extends Component
{
    #[Validate('required|min:3')]
    public string $company_name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:8')]
    public string $password = '';

    #[Validate('required|in:start,pro')]
    public string $plan = 'start';

    public function submit(StripeService $stripeService)
    {
        $this->validate();

        // Security: We DO NOT pass passwords to Stripe Metadata.
        // We temporarily store the payload locally.
        $registrationId = (string) Str::uuid();
        
        Cache::put("registration_{$registrationId}", [
            'company_name' => $this->company_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'plan' => $this->plan,
        ], now()->addHours(2));

        return $stripeService->createCheckoutSessionForSubscription($registrationId, $this->plan);
    }

    public function render()
    {
        return <<<'HTML'
        <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans text-slate-800">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-blue-900">
                    Create your Master-Digit Account
                </h2>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Register and subscribe to start managing your logistics.
                </p>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border-t-4 border-blue-600">
                    <form wire:submit="submit" class="space-y-6">
                        
                        <flux:input wire:model="company_name" label="Company Name" placeholder="Logistics Agency LLC" required />
                        
                        <flux:input wire:model="email" type="email" label="Admin Email" placeholder="admin@example.com" required />
                        
                        <flux:input wire:model="password" type="password" label="Password" required />

                        <flux:radio.group wire:model="plan" label="Subscription Plan" required>
                            <flux:radio value="start" label="Start Plan (€49/mo)" description="Perfect for small logistics teams." />
                            <flux:radio value="pro" label="Pro Plan (€149/mo)" description="Advanced features for enterprise." />
                        </flux:radio.group>

                        <div class="pt-4">
                            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submit">Proceed to Payment</span>
                                <span wire:loading wire:target="submit">Processing...</span>
                            </flux:button>
                        </div>
                    </form>
                    
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-slate-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-slate-500">Secure Payments via Stripe</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
