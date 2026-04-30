<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Tenant;
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
        $tenant = Tenant::factory()->create();
        
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'SEO Audit',
            'description' => 'Comprehensive SEO report',
            'price' => 500.00,
            'type' => ProductType::SERVICE,
            'is_recurring' => false,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'SEO Audit',
        ]);
        
        $this->assertIsString($product->id);
        $this->assertEquals(26, strlen($product->id));
    }

    public function test_invoice_item_total_is_calculated_automatically()
    {
        $tenant = Tenant::factory()->create();
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'SEO Audit',
            'price' => 100.00,
            'type' => ProductType::SERVICE,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_type' => User::class,
            'customer_id' => $user->id,
            'invoice_number' => 'INV-001',
            'due_date' => now()->addDays(14),
            'status' => InvoiceStatus::DRAFT,
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => 'SEO Audit Item',
            'quantity' => 2,
            'unit_price' => 100.00,
            'tax_rate' => 21.00,
        ]);

        // 2 * 100 * 1.21 = 242.00
        $this->assertEquals(242.00, $item->total);
    }

    public function test_invoice_can_belong_to_lead_or_user()
    {
        $tenant = Tenant::factory()->create();
        
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'company_name' => 'Test Corp',
            'contact_person' => 'John Doe',
        ]);

        $invoiceUser = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_type' => User::class,
            'customer_id' => $user->id,
            'invoice_number' => 'INV-USER',
            'due_date' => now(),
        ]);

        $invoiceLead = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_type' => Lead::class,
            'customer_id' => $lead->id,
            'invoice_number' => 'INV-LEAD',
            'due_date' => now(),
        ]);

        $this->assertInstanceOf(User::class, $invoiceUser->customer);
        $this->assertInstanceOf(Lead::class, $invoiceLead->customer);
    }
}
