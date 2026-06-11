<div>
    @if($unseenCount > 0)
        {{-- Real-time notification banner — appears when WebSocket lead events are received --}}
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    {{-- Pulsing dot indicating live WebSocket connection --}}
                    <span class="relative flex h-3 w-3 mt-1">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-green-800">
                            {{ $unseenCount }} new {{ Str::plural('lead', $unseenCount) }} received via WebSocket
                        </p>
                        <p class="text-xs text-green-600 mt-0.5">
                            Real-time via Laravel Reverb — no page refresh needed
                        </p>
                    </div>
                </div>
                <button
                    wire:click="clearNotifications"
                    class="text-xs text-green-700 hover:text-green-900 font-medium underline transition-colors"
                >
                    Clear
                </button>
            </div>

            @foreach($latestLeads as $lead)
                <div class="mt-3 pt-3 border-t border-green-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-900">
                            🚛 {{ $lead['company_name'] }}
                            <span class="ml-2 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                {{ ucfirst($lead['package']) }}
                            </span>
                        </p>
                        <p class="text-xs text-green-700 mt-0.5">
                            {{ $lead['contact_person'] }} — {{ $lead['email'] }}
                        </p>
                    </div>
                    <span class="text-xs text-green-500 font-mono">{{ $lead['received_at'] }}</span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Idle state — show WebSocket connection indicator --}}
        <div class="mb-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
            </span>
            <p class="text-xs text-slate-500">
                Reverb WebSocket connected — waiting for incoming leads in real time
            </p>
        </div>
    @endif
</div>
