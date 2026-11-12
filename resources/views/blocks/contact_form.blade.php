@php
    $data = $block['data'] ?? [];
    $title = $data['title'] ?? 'Neem Contact Op';
    $subtitle = $data['subtitle'] ?? 'Laat uw bericht achter en wij reageren zo spoedig mogelijk.';
    $btnText = $data['submit_button_text'] ?? 'Verzend Bericht';
@endphp

<section id="contact" class="py-20 bg-slate-900 border-t border-slate-800">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">
                {{ $title }}
            </h2>
            <p class="text-slate-400 text-base max-w-xl mx-auto">
                {{ $subtitle }}
            </p>
        </div>

        @if(session('contact_success'))
            <div class="p-4 mb-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-center font-medium">
                {{ session('contact_success') }}
            </div>
        @endif

        <form action="{{ route('public.contact.submit') }}" method="POST" class="p-8 rounded-2xl bg-slate-850 border border-slate-800 space-y-6">
            @csrf
            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">

            <!-- Honeypot -->
            <div class="hidden">
                <input type="text" name="website_hp" value="">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-300 mb-2">Uw Naam / Bedrijf *</label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">E-mailadres *</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-300 mb-2">Telefoonnummer</label>
                <input type="tel" id="phone" name="phone" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label for="message" class="block text-sm font-semibold text-slate-300 mb-2">Uw Vraag of Aanvraag *</label>
                <textarea id="message" name="message" rows="4" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"></textarea>
            </div>

            <button type="submit" class="w-full py-4 rounded-xl font-bold text-base bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-500/25 transition-all duration-200">
                {{ $btnText }}
            </button>
        </form>
    </div>
</section>
