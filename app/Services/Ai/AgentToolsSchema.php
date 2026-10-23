<?php

namespace App\Services\Ai;

use App\Actions\Invoices\SendCommercialDocumentAction;
use App\Actions\Pages\BuildPageBlocksAction;
use App\Actions\Tenants\ProvisionTenantAction;
use App\Models\CatalogItem;
use App\Models\Invoice;
use App\Models\Page;
use App\Support\TemplateBlocks;
use Illuminate\Support\Str;

class AgentToolsSchema
{
    public function __construct(
        protected ProvisionTenantAction $provisionTenantAction,
        protected BuildPageBlocksAction $buildPageBlocksAction,
        protected SendCommercialDocumentAction $sendCommercialDocumentAction
    ) {}

    /**
     * Get JSON schemas for Gemini Function Calling / OpenAI Tools.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getToolDeclarations(): array
    {
        return [
            [
                'name' => 'tool_provision_tenant',
                'description' => 'Atomically onboards a new Flemish company tenant, generates owner user, seeds Belgian Peppol tax rates, and builds the initial homepage with blocks.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'company_name' => [
                            'type' => 'string',
                            'description' => 'The official registered company name (e.g. "De Smet Transport BV").',
                        ],
                        'niche' => [
                            'type' => 'string',
                            'enum' => [
                                TemplateBlocks::NICHE_TRANSPORT,
                                TemplateBlocks::NICHE_BREEDING,
                                TemplateBlocks::NICHE_CRAFTSMAN,
                                TemplateBlocks::NICHE_BLANK,
                            ],
                            'description' => 'The target business sector for pre-configured blocks.',
                        ],
                        'plan' => [
                            'type' => 'string',
                            'enum' => ['start', 'pro', 'scale'],
                            'description' => 'The subscription tier.',
                        ],
                        'admin_name' => [
                            'type' => 'string',
                            'description' => 'The full name of the company administrator.',
                        ],
                        'admin_email' => [
                            'type' => 'string',
                            'description' => 'Contact and login email address for the owner.',
                        ],
                        'vat_number' => [
                            'type' => 'string',
                            'description' => 'Belgian VAT number (e.g. "BE0123456789").',
                        ],
                        'kbo_number' => [
                            'type' => 'string',
                            'description' => 'Belgian KBO enterprise number.',
                        ],
                        'tagline' => [
                            'type' => 'string',
                            'description' => 'Short marketing slogan or tagline for the homepage.',
                        ],
                    ],
                    'required' => ['company_name', 'admin_name', 'admin_email'],
                ],
            ],
            [
                'name' => 'tool_build_page_blocks',
                'description' => 'Updates the visual block layout of a tenant website page with validated and sanitized JSON blocks.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'page_id' => [
                            'type' => 'string',
                            'description' => 'The ULID identifier of the page to update.',
                        ],
                        'blocks' => [
                            'type' => 'array',
                            'description' => 'List of block definitions (each with id, type, and data object).',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'type' => ['type' => 'string'],
                                    'data' => ['type' => 'object'],
                                ],
                                'required' => ['id', 'type', 'data'],
                            ],
                        ],
                    ],
                    'required' => ['page_id', 'blocks'],
                ],
            ],
            [
                'name' => 'tool_create_catalog_item',
                'description' => 'Creates a new product or service in the tenant catalog, automatically scoping to the tenant.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                            'description' => 'Product or service title.',
                        ],
                        'price_eur' => [
                            'type' => 'number',
                            'description' => 'Price in EUR (e.g. 1500.00). Will be stored in cents.',
                        ],
                        'description' => [
                            'type' => 'string',
                            'description' => 'Detailed product description.',
                        ],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['available', 'reserved', 'sold'],
                            'description' => 'Current inventory or availability status.',
                        ],
                        'category_id' => [
                            'type' => 'string',
                            'description' => 'Optional ULID of the category.',
                        ],
                    ],
                    'required' => ['name', 'price_eur'],
                ],
            ],
            [
                'name' => 'tool_send_invoice_payment_link',
                'description' => 'Finalizes an invoice, generates the PDF, and sends the payment link email to the customer with Bancontact / Card.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'invoice_id' => [
                            'type' => 'string',
                            'description' => 'The ULID identifier of the invoice to dispatch.',
                        ],
                        'recipient_email' => [
                            'type' => 'string',
                            'description' => 'Optional override for the customer email address.',
                        ],
                    ],
                    'required' => ['invoice_id'],
                ],
            ],
        ];
    }

    /**
     * Dispatch and execute an agent tool call securely within tenant boundaries.
     *
     * @param  string      $toolName
     * @param  array       $arguments
     * @param  string|null $tenantId
     * @param  string|null $operatorUserId
     * @return array<string, mixed>
     */
    public function handleToolCall(
        string $toolName,
        array $arguments,
        ?string $tenantId = null,
        ?string $operatorUserId = null
    ): array {
        return match ($toolName) {
            'tool_provision_tenant' => $this->executeProvisionTenant($arguments, $operatorUserId),
            'tool_build_page_blocks' => $this->executeBuildPageBlocks($arguments, $tenantId, $operatorUserId),
            'tool_create_catalog_item' => $this->executeCreateCatalogItem($arguments, $tenantId),
            'tool_send_invoice_payment_link' => $this->executeSendInvoicePaymentLink($arguments, $tenantId, $operatorUserId),
            default => [
                'status' => 'error',
                'message' => "Onbekend AI-gereedschap: {$toolName}",
            ],
        };
    }

    protected function executeProvisionTenant(array $args, ?string $operatorUserId): array
    {
        $args['provisioned_by'] = 'agent';
        $args['operator_user_id'] = $operatorUserId;

        $result = $this->provisionTenantAction->execute($args);

        return [
            'status' => 'success',
            'tenant_id' => $result['tenant']->id,
            'tenant_slug' => $result['tenant']->slug,
            'owner_email' => $result['user']->email,
            'plain_password' => $result['plain_password'],
            'homepage_url' => url("/?tenant={$result['tenant']->slug}"),
        ];
    }

    protected function executeBuildPageBlocks(array $args, ?string $tenantId, ?string $operatorUserId): array
    {
        $page = Page::where('id', $args['page_id'])
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->firstOrFail();

        $updatedPage = $this->buildPageBlocksAction->execute($page, $args['blocks'], $operatorUserId);

        return [
            'status' => 'success',
            'page_id' => $updatedPage->id,
            'block_count' => count($updatedPage->blocks ?? []),
        ];
    }

    protected function executeCreateCatalogItem(array $args, ?string $tenantId): array
    {
        if (!$tenantId) {
            return [
                'status' => 'error',
                'message' => 'Geen tenant context beschikbaar voor het aanmaken van een catalogusitem.',
            ];
        }

        $priceMinor = isset($args['price_eur']) ? (int) round($args['price_eur'] * 100) : null;
        $slug = Str::slug($args['name']);

        $item = CatalogItem::create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $tenantId,
            'category_id' => $args['category_id'] ?? null,
            'name' => $args['name'],
            'slug' => $slug,
            'price_minor' => $priceMinor,
            'description' => $args['description'] ?? null,
            'status' => $args['status'] ?? 'available',
        ]);

        return [
            'status' => 'success',
            'item_id' => $item->id,
            'name' => $item->name,
            'price_eur' => $priceMinor ? ($priceMinor / 100) : null,
        ];
    }

    protected function executeSendInvoicePaymentLink(array $args, ?string $tenantId, ?string $operatorUserId): array
    {
        $invoice = Invoice::where('id', $args['invoice_id'])
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->firstOrFail();

        $sentInvoice = $this->sendCommercialDocumentAction->execute(
            $invoice,
            $args['recipient_email'] ?? null,
            $operatorUserId
        );

        return [
            'status' => 'success',
            'invoice_id' => $sentInvoice->id,
            'status' => $sentInvoice->status,
            'payment_token' => $sentInvoice->payment_token,
            'payment_url' => url("/pay/{$sentInvoice->payment_token}"),
        ];
    }
}
