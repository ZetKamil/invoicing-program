{{-- =====================================================================
     Tenant Switcher – injected via PanelsRenderHook::USER_MENU_BEFORE.
     Uses native Filament Blade components (x-filament::dropdown.*).
     ===================================================================== --}}
<div>
    @if($isImpersonating)
        {{-- Active impersonation badge + leave button --}}
        <div style="display: flex; align-items: center; margin-right: 1rem;">
            <x-filament::badge color="warning" icon="heroicon-m-user-circle">
                {{ auth()->user()->tenant?->name }}
            </x-filament::badge>
        </div>
    @endif

    @if($isSuperAdmin && !$isImpersonating && $tenants->isNotEmpty())
        {{-- Native Filament dropdown: aligns in topbar --}}
        <div class="flex items-center mr-2">
            <x-filament::dropdown placement="bottom-end" :teleport="true">
                <x-slot name="trigger">
                    <x-filament::icon-button
                        icon="heroicon-m-building-office-2"
                        color="gray"
                        size="sm"
                        :tooltip="__('Inloggen als onderneming')"
                        label="Inloggen als onderneming"
                    />
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach($tenants as $t)
                        <x-filament::dropdown.list.item
                            wire:click="impersonateTenant('{{ $t->id }}')"
                            icon="heroicon-o-arrow-right-on-rectangle"
                        >
                            {{ $t->name }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>
    @endif
</div>
