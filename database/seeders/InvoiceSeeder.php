<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'logi-web-pro')->first();

        if ($tenant) {
            $leads = Lead::where('tenant_id', $tenant->id)->take(3)->get();
            $products = Product::where('tenant_id', $tenant->id)->get();

            if ($leads->isEmpty() || $products->isEmpty()) {
                return;
            }

            foreach ($leads as $index => $lead) {
                // Determine a random status
                $status = collect([InvoiceStatus::DRAFT, InvoiceStatus::SENT, InvoiceStatus::PAID])->random();
                
                $invoice = Invoice::create([
                    'tenant_id' => $tenant->id,
                    'customer_type' => Lead::class,
                    'customer_id' => $lead->id,
                    'invoice_number' => 'INV-2026-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'subtotal' => 0, 
                    'tax_total' => 0,
                    'total_amount' => 0,
                    'due_date' => now()->addDays(30),
                    'status' => $status,
                ]);

                // Add a product to the invoice
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->price > 0 ? $product->price : 1000.00;
                $taxRate = 21; // 21% VAT
                $total = $quantity * $unitPrice;
                $taxTotal = $total * ($taxRate / 100);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'description' => $product->name . ' Setup & License',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'total' => $total,
                ]);

                // Update invoice totals (in case observer doesn't bubble up to Invoice model)
                $invoice->update([
                    'subtotal' => $total,
                    'tax_total' => $taxTotal,
                    'total_amount' => $total + $taxTotal,
                    'paid_at' => $status === InvoiceStatus::PAID ? now() : null,
                ]);
            }
        }
    }
}
