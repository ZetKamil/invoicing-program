<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Bedrijf;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $bedrijf = Bedrijf::where('slug', 'logi-web-pro')->first();

        if ($bedrijf) {
            $leads = Lead::where('bedrijf_id', $bedrijf->id)->take(3)->get();
            $products = Product::where('bedrijf_id', $bedrijf->id)->get();

            if ($leads->isEmpty() || $products->isEmpty()) {
                return;
            }

            foreach ($leads as $index => $lead) {
                // Determine a random status
                $status = collect([InvoiceStatus::DRAFT, InvoiceStatus::SENT, InvoiceStatus::PAID])->random();
                
                $invoice = Invoice::create([
                    'bedrijf_id' => $bedrijf->id,
                    'customer_type' => Lead::class,
                    'customer_id' => $lead->id,
                    'invoice_number' => 'INV-2026-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'subtotal' => 0, 
                    'tax_total' => 0,
                    'total_amount' => 0,
                    'due_date' => now()->addDays(30),
                    'status' => $status,
                    'trailer_type'   => collect(['Huifwagen', 'Koelwagen', 'Container', 'Dieplader', 'Silo', 'Kipper', 'Tankwagen'])->random(),
                    'cargo_weight_kg' => rand(1000, 24000),
                    'pallet_count' => rand(1, 33),
                    'loading_date' => now()->addDays(rand(1, 14))->format('Y-m-d'),
                    'delivery_date' => now()->addDays(rand(15, 30))->format('Y-m-d'),
                    'loading_address' => collect(['Wetstraat 16, 1000 Brussel, BE', 'Meir 1, 2000 Antwerpen, BE', 'Veldstraat 2, 9000 Gent, BE'])->random(),
                    'delivery_address' => collect(['Damrak 1, 1012 LG Amsterdam, NL', 'Coolsingel 1, 3012 AA Rotterdam, NL', 'Champs-Élysées 1, 75008 Parijs, FR'])->random(),
                    'cmr_number'     => 'CMR-' . rand(100000, 999999),
                    'truck_license_plate'  => '1-' . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . '-' . rand(100, 999),
                    'trailer_license_plate' => 'Q-' . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . '-' . rand(100, 999),
                    'notes'          => 'Chauffeur meldde een lichte vertraging bij het laden.',
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
                    'stripe_payment_intent_id' => $status === InvoiceStatus::PAID ? 'pi_seeder_' . \Illuminate\Support\Str::random(17) : null,
                ]);
            }
        }
    }
}
