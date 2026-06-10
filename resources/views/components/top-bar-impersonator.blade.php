<?php

use Livewire\Volt\Component;
use App\Models\User;
use Filament\Facades\Filament;
use STS\FilamentImpersonate\Facades\Impersonation;

new class extends Component {
    public ?string $selectedUserId = '';

    public function updatedSelectedUserId($value)
    {
        if (!$value) return;

        $targetUser = User::find($value);
        if (!$targetUser) return;

        $currentUser = Filament::auth()->user();

        if ($currentUser && $currentUser->is_super_admin) {
            session()->put([
                'impersonate.back_to' => request()->header('referer') ?? Filament::getCurrentOrDefaultPanel()->getUrl(),
                'impersonate.guard' => Filament::getCurrentOrDefaultPanel()->getAuthGuard(),
            ]);

            if (Impersonation::enter($currentUser, $targetUser, Filament::getCurrentOrDefaultPanel()->getAuthGuard())) {
                $this->redirect(Filament::getCurrentOrDefaultPanel()->getUrl());
            }
        }
    }

    public function with(): array
    {
        return [
            'users' => auth()->user()?->is_super_admin 
                        ? User::with('tenant')->get() 
                        : [],
        ];
    }
}; ?>

@if(auth()->user()?->is_super_admin && !\STS\FilamentImpersonate\Facades\Impersonation::isImpersonating())
<div class="relative flex items-center ms-4">
    <select wire:model.live="selectedUserId" 
            class="text-sm bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            style="min-width: 200px;">
        <option value="">Wybierz klienta (Impersonate)...</option>
        @foreach($users as $u)
            @if($u->id !== auth()->id())
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->tenant?->name ?? 'Brak' }})</option>
            @endif
        @endforeach
    </select>
</div>
@endif
