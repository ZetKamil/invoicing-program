<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Bedrijf;
use App\Models\User;
use App\Enums\ProductType;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product_with_ulid()
    {
        $bedrijf = Bedrijf::factory()->create();
        
        $product = Product::create([
            'bedrijf_id' => $bedrijf->id,
            'name' => 'Transport Service',
            'description' => 'Pallet transport',
            'price' => 500.00,
            'type' => ProductType::SERVICE,
            'is_recurring' => false,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Transport Service',
        ]);
        
        $this->assertIsString($product->id);
        $this->assertEquals(26, strlen($product->id));
    }

    public function test_invoice_totals_are_calculated_by_observer()
    {
        $bedrijf = Bedrijf::factory()->create();
        $product = Product::create([
            'bedrijf_id' => $bedrijf->id,
            'name' => 'Pallet Transport',
            'price' => 100.00,
            'type' => ProductType::SERVICE,
        ]);

        $user = User::factory()->create(['bedrijf_id' => $bedrijf->id]);
        
        $invoice = Invoice::create([
            'bedrijf_id' => $bedrijf->id,
            'customer_type' => User::class,
            'customer_id' => $user->id,
            'invoice_number' => 'INV-001',
            'due_date' => now()->addDays(14),
            'status' => InvoiceStatus::DRAFT,
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => 'Transport',
            'quantity' => 2,
            'unit_price' => 100.00,
            'tax_rate' => 21.00,
            // total is calculated by observer, so we don't set it here explicitly
        ]);

        // Refresh the models to see the observer's changes
        $item->refresh();
        $invoice->refresh();

        // 2 * 100 = 200 (subtotal), 200 * 0.21 = 42 (tax), total = 242
        $this->assertEquals(242.00, $item->total);
        $this->assertEquals(200.00, $invoice->subtotal);
        $this->assertEquals(42.00, $invoice->tax_total);
        $this->assertEquals(242.00, $invoice->total_amount);
    }

    public function test_invoice_can_belong_to_lead_or_user()
    {
        $bedrijf = Bedrijf::factory()->create();
        
        $user = User::factory()->create(['bedrijf_id' => $bedrijf->id]);
        $lead = Lead::create([
            'bedrijf_id' => $bedrijf->id,
            'company_name' => 'Test Corp',
            'contact_person' => 'John Doe',
        ]);

        $invoiceUser = Invoice::create([
            'bedrijf_id' => $bedrijf->id,
            'customer_type' => User::class,
            'customer_id' => $user->id,
            'invoice_number' => 'INV-USER',
            'due_date' => now(),
        ]);

        $invoiceLead = Invoice::create([
            'bedrijf_id' => $bedrijf->id,
            'customer_type' => Lead::class,
            'customer_id' => $lead->id,
            'invoice_number' => 'INV-LEAD',
            'due_date' => now(),
        ]);

        $this->assertInstanceOf(User::class, $invoiceUser->customer);
        $this->assertInstanceOf(Lead::class, $invoiceLead->customer);
    }
}
