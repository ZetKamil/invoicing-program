<div>
    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 font-sans text-slate-800">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border-t-4 border-blue-600 overflow-hidden">
            
            <!-- Header -->
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-blue-600">{{ $invoice->tenant->name ?? 'Logistics Provider' }}</h1>
                    <p class="text-sm text-slate-500 mt-1">Invoice Payment Portal</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                        {{ $invoice->status->value === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ strtoupper($invoice->status->value) }}
                    </span>
                </div>
            </div>

            <!-- Body -->
            <div class="px-8 py-8">
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Billed To</h3>
                        <p class="font-medium text-slate-900">{{ $invoice->customer->company_name ?? $invoice->customer->name ?? 'Customer' }}</p>
                        <p class="text-slate-600 text-sm mt-1">{{ $invoice->customer->email ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Invoice Details</h3>
                        <p class="text-slate-900"><span class="font-medium">Number:</span> {{ $invoice->invoice_number }}</p>
                        <p class="text-slate-600 text-sm mt-1"><span class="font-medium">Date:</span> {{ $invoice->created_at->format('M d, Y') }}</p>
                        <p class="text-slate-600 text-sm"><span class="font-medium">Due:</span> {{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="mb-8 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-100">
                                <th class="py-3 font-semibold text-slate-600">Description</th>
                                <th class="py-3 font-semibold text-slate-600 text-right">Qty</th>
                                <th class="py-3 font-semibold text-slate-600 text-right">Price</th>
                                <th class="py-3 font-semibold text-slate-600 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr class="border-b border-slate-50">
                                    <td class="py-4 text-slate-800">{{ $item->description }}</td>
                                    <td class="py-4 text-slate-600 text-right">{{ $item->quantity }}</td>
                                    <td class="py-4 text-slate-600 text-right">€{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-4 text-slate-800 font-medium text-right">€{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-slate-500 text-center italic">No items listed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="flex justify-end mb-8">
                    <div class="w-1/2 max-w-sm">
                        <div class="flex justify-between py-2 text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span>€{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-sm text-slate-600 border-b border-slate-100">
                            <span>Tax (21%)</span>
                            <span>€{{ number_format($invoice->tax_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-3 text-lg font-bold text-slate-900 border-b-2 border-slate-800">
                            <span>Total Due</span>
                            <span class="text-blue-600">€{{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action -->
                <div class="mt-10 flex flex-col items-center justify-center space-y-4">
                    @if($invoice->status->value !== 'paid')
                        <button wire:click="pay" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                            <span wire:loading.remove wire:target="pay">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                Pay with Card (Stripe)
                            </span>
                            <span wire:loading wire:target="pay" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                        <p class="text-xs text-slate-500 flex items-center mt-3">
                            <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Payments are securely processed by Stripe.
                        </p>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 w-full text-center">
                            <svg class="w-12 h-12 text-green-50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h3 class="text-lg font-medium text-green-800">Payment Received</h3>
                            <p class="text-green-600 mt-1">This invoice was paid on {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : 'a previous date' }}. Thank you!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
    </div>
</div>
