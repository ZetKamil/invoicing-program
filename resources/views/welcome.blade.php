<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logi-Web PRO — Uw transportbedrijf Digitaal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
</head>
<body class="bg-[#08111F] text-white font-['DM_Sans'] antialiased">
    <livewire:public.package-inquiry-form />

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-[#08111F]/80 backdrop-blur-md border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-['Outfit'] font-bold tracking-tight">TransDigit<span class="text-orange-500">.</span></span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#services" class="text-sm font-medium text-white/70 hover:text-white transition-colors">Diensten</a>
                    <a href="#hoewerkhet" class="text-sm font-medium text-white/70 hover:text-white transition-colors">Hoe het werkt</a>
                    <a href="#smartquote" class="text-sm font-medium text-white/70 hover:text-white transition-colors">Smart Quote</a>
                    <a href="#about" class="text-sm font-medium text-white/70 hover:text-white transition-colors">Over ons</a>
                </div>
                <div class="flex items-center gap-4">
                    <flux:button variant="primary" onclick="document.getElementById('services').scrollIntoView({behavior:'smooth'})">
                        Gratis audit →
                    </flux:button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="relative py-24 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-blue-600/10 blur-[120px] rounded-full"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-medium mb-8">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    Logi-Web PRO — West-Vlaanderen
                </div>
                <h1 class="text-5xl md:text-7xl font-['Outfit'] font-bold leading-[1.1] mb-6">
                    Uw transportbedrijf.<br>
                    Digitaal.<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">Zonder chaos.</span>
                </h1>
                <p class="text-lg text-white/60 mb-10 leading-relaxed max-w-2xl">
                    Ultrasnelle websites met een ingebouwd Smart Quote-systeem voor Vlaamse transporteurs. Uw dispatcher ontvangt enkel nog volledige, gestructureerde offerteaanvragen.
                </p>
                <div class="flex flex-wrap gap-4">
                    <flux:button variant="primary">🔍 Gratis AI-audit aanvragen</flux:button>
                    <flux:button variant="ghost" onclick="document.getElementById('services').scrollIntoView({behavior:'smooth'})">Bekijk pakketten</flux:button>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="services" class="py-24 bg-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-blue-400 text-sm font-semibold tracking-wider uppercase">Onze pakketten</span>
                <h2 class="text-4xl font-['Outfit'] font-bold mt-4 mb-6">Transparante prijzen.<br>Geen verrassingen.</h2>
                <p class="text-white/60">Elk pakket bevat hosting, frontend én een Mini-CRM dashboard.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Start -->
                <div class="p-8 rounded-3xl bg-[#0D1E35] border border-white/5 flex flex-col h-full">
                    <div class="text-xl font-bold mb-4">Start</div>
                    <div class="text-4xl font-bold mb-2">€850</div>
                    <div class="text-white/40 text-sm mb-8">eenmalig + €49/maand</div>
                    <ul class="space-y-4 mb-10 flex-grow">
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            Professionele one-pager
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            Basis offerteformulier
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            SEO-optimalisatie
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            Hosting inbegrepen
                        </li>
                    </ul>
                    <flux:button variant="ghost" class="w-full" x-on:click="$dispatch('open-modal', { id: 'package-inquiry', package: 'Start' })" onclick="window.Livewire.find(document.querySelector('[wire\\:id]').id).call('open', 'Start')">Kies Start</flux:button>
                </div>

                <!-- Pro -->
                <div class="p-8 rounded-3xl bg-blue-600 border border-blue-400/30 flex flex-col h-full relative overflow-hidden">
                    <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">Populair</div>
                    <div class="text-xl font-bold mb-4">Pro Transport</div>
                    <div class="text-4xl font-bold mb-2">€1.150</div>
                    <div class="text-white/80 text-sm mb-8">eenmalig + €79/maand</div>
                    <ul class="space-y-4 mb-10 flex-grow">
                        <li class="flex items-center gap-3 text-sm text-white/90">
                            <flux:icon.check class="w-5 h-5 text-white" />
                            5 pagina's op maat
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/90">
                            <flux:icon.check class="w-5 h-5 text-white" />
                            Smart Quote System
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/90">
                            <flux:icon.check class="w-5 h-5 text-white" />
                            Mini-CRM dashboard
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/90">
                            <flux:icon.check class="w-5 h-5 text-white" />
                            Live in 7 werkdagen
                        </li>
                    </ul>
                    <flux:button variant="primary" class="w-full bg-white text-blue-600 hover:bg-white/90" onclick="window.Livewire.find(document.querySelector('[wire\\:id]').id).call('open', 'Pro Transport')">Kies Pro Transport</flux:button>
                </div>

                <!-- Enterprise -->
                <div class="p-8 rounded-3xl bg-[#0D1E35] border border-white/5 flex flex-col h-full">
                    <div class="text-xl font-bold mb-4">Enterprise</div>
                    <div class="text-4xl font-bold mb-2">€1.950+</div>
                    <div class="text-white/40 text-sm mb-8">eenmalig + €149/maand</div>
                    <ul class="space-y-4 mb-10 flex-grow">
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            Volledige automatisering
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            E-facturatiemodule
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            Chauffeursbeheer
                        </li>
                        <li class="flex items-center gap-3 text-sm text-white/70">
                            <flux:icon.check class="w-5 h-5 text-green-500" />
                            TMS-uitbreiding
                        </li>
                    </ul>
                    <flux:button variant="ghost" class="w-full" onclick="window.Livewire.find(document.querySelector('[wire\\:id]').id).call('open', 'Enterprise')">Vraag offerte aan</flux:button>
                </div>
            </div>
        </div>
    </section>

    @fluxScripts
</body>
</html>
