{{-- =====================================================================
     Bedrijf Switcher â€“ injected via PanelsRenderHook::USER_MENU_BEFORE.
     Uses native Filament v5 Blade components only (x-filament::dropdown.*).
     No raw Alpine.js or custom Tailwind blocks that break the topbar grid.
     ===================================================================== --}}
<div>
    @if($isImpersonating)
        {{-- Active impersonation badge + leave button --}}
        <div style="display: flex; align-items: center; margin-right: 1rem;">
            <x-filament::badge color="warning" icon="heroicon-m-user-circle">
                {{ auth()->user()->bedrijf?->name }}
            </x-filament::badge>
        </div>
    @endif

    @if($isSuperAdmin && !$isImpersonating && $bedrijven->isNotEmpty())
        {{-- Native Filament dropdown: aligns perfectly in the topbar --}}
        <div class="flex items-center mr-2">
            <x-filament::dropdown placement="bottom-end" :teleport="true">
                <x-slot name="trigger">
                    <x-filament::icon-button
                        icon="heroicon-m-building-office-2"
                        color="gray"
                        size="sm"
                        :tooltip="__('Inloggen als klant')"
                        label="Inloggen als klant"
                    />
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach($bedrijven as $bedrijf)
                        <x-filament::dropdown.list.item
                            wire:click="impersonateBedrijf('{{ $bedrijf->id }}')"
                            icon="heroicon-o-arrow-right-on-rectangle"
                        >
                            {{ $bedrijf->name }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>
    @endif
</div>
