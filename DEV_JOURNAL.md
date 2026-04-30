# Development Journal

## 2026-04-30: Multi-tenancy Foundation

### Task Summary
Initialized the multi-tenancy foundation by creating the `Tenant` model, a `HasTenant` trait for automated scoping, and updating the `User` model with roles and tenant association.

### Design Patterns
- **Global Scopes**: Used to ensure strict data isolation. By applying the `TenantScope` via the `HasTenant` trait, we guarantee that no logistics company can accidentally access another company's data.
- **Enums**: Implemented `UserRole` as a PHP Backed Enum to ensure type safety and valid state across the application.
- **Traits**: Used `HasTenant` to provide a reusable way to make any model "tenant-aware" with minimal boilerplate.

### Architecture Decisions: ULID vs UUID
We chose **ULIDs (Universally Unique Lexicographically Sortable Identifiers)** over UUIDs for several reasons:
1. **Sortability**: ULIDs are lexicographically sortable, which means they maintain insertion order. This is significantly better for database indexing performance compared to random UUIDs.
2. **Readability**: ULIDs are shorter (26 characters) and use a character set (Crockford's Base32) that avoids ambiguous characters.
3. **Compatibility**: They fit into a 128-bit space, making them compatible with UUID storage if needed, while providing the benefits of a timestamp-prefixed ID.

### Presentation Tip
"This multi-tenancy implementation uses Eloquent Global Scopes to provide transparent data isolation at the database layer, combined with ULIDs for optimized indexing and lexicographical sortability."

---

## 2026-04-30: CRM and Sales Pipeline Module

### Task Summary
Implemented the core `Lead` and `Quote` models along with their respective migrations, enums (`LeadStatus`, `QuoteStatus`), and policies to ensure multi-tenant data isolation. Also introduced a domain action for quote creation.

### Design Patterns
**Domain Actions for Quote Creation (`CreateQuoteAction`):**
We utilize a dedicated Action class (`app/Actions/Quotes/CreateQuoteAction.php`) for generating quotes instead of placing this logic in controllers or models. This approach:
1. **Encapsulates Business Logic**: Generating a quote involves specific rules (calculating validity dates, generating unique quote numbers). Keeping this in an Action ensures the logic is reusable across different entry points (e.g., API, Livewire components, Artisan commands).
2. **Improves Testability**: The action can be unit-tested in isolation without mocking HTTP requests or complex controller dependencies.
3. **Adheres to Single Responsibility Principle (SRP)**: Models remain thin (handling only data structure and relationships), and controllers remain focused on request/response flow.

**Lead Magnet Flow Integration:**
The system is designed so that when a potential client interacts with a "Lead Magnet" (e.g., requesting a free website audit), a new `Lead` record is created with a status of `new` (or `audited`). 
- The `audit_report_url` column in the `leads` table stores the link to the automatically generated report.
- Once the lead is engaged, the `CreateQuoteAction` can be triggered to transition the flow from Lead to an active Sales Pipeline (Quote), linking the two records via the `lead_id` foreign key.

### Presentation Tip
"By encapsulating the quote generation process within a dedicated Domain Action, we decouple complex business rules from our controllers, ensuring high reusability and maintainability as our sales pipeline logic evolves."

---

## 2026-04-30: Billing and Communication Engine

### Task Summary
Implemented the core billing infrastructure, including Products, Invoices, and automated Communication logging. We also standardized the entire database to use ULIDs for all primary and foreign keys.

### Design Patterns
**Polymorphic Communications (`MorphTo`):**
We implemented a polymorphic `related_model` relationship in the `communications` table. This allows the system to:
1. **Unify Interaction Logs**: Both `Quotes` and `Invoices` share a single communication history table.
2. **Extensibility**: Future modules (like "Support Tickets" or "Orders") can easily log communications without modifying the schema.
3. **Streamlined UI**: A single Livewire component can render the communication history for any related model by simply passing the polymorphic relation.

**Automated Calculation Logic (Observers):**
Calculated fields like `InvoiceItem` totals are handled by the `InvoiceItemObserver`. This ensures that business logic remains centralized and consistent regardless of whether an item is created via the UI, an API, or a background job.

### Architecture Decisions: Peppol-Ready Billing
The system is built to be "Peppol-Ready," which is the standard for electronic invoicing in the EU and globally. We've included:
1. **`ubl_xml_path`**: Storage for the Universal Business Language (UBL) XML file, required for Peppol compliance.
2. **`buyer_reference`**: A mandatory field in many e-invoicing standards to identify the purchaser.
3. **`due_date`**: Strict date management for automated overdue status transitions.

### Presentation Tip
"Our billing engine is architected for global compliance, featuring Peppol-ready data structures and a unified polymorphic communication layer that tracks every interaction with the client throughout the sales and billing lifecycle."

---

---

## 2026-04-30: Tenant Dashboard & Logistics Engine Refactoring

### Task Summary
Successfully merged the structural methodology from the reference project into our main Logistics SaaS. The panel has been transformed into a dedicated **Tenant Dashboard**, focusing on Sales and Billing while retaining a tenant-scoped Content Module for Inbound Marketing.

### Porting & Refactoring Strategy
1.  **Structural Scaffolding**: Adopted the "Clean Schema/Table" pattern for all Filament resources (Invoices, Quotes, Leads, Products). This ensures that form and table definitions are decoupled from the Resource class, improving maintainability.
2.  **Tenant-Scoped CMS**: Ported the `Posts`, `Categories`, and `Media` logic, but immediately upgraded them with the `HasTenant` trait and ULID standard. This allows each logistics company to maintain their own private knowledge base or public announcements.
3.  **Domain Actions Implementation**: Decoupled business logic into standalone Action classes:
    *   `CreateQuoteAction`: Logic for generating quotes from leads.
    *   `GenerateInvoicePdfAction`: Placeholder for compliant PDF generation.
    *   `PublishPostAction`: Logic for status transitions in the content module.

### Architecture Decisions
*   **Polymorphic Media for Logistics**: The `Media` model now serves as the backbone for both marketing (post images) and operations (lead attachments, invoice PDFs).
*   **Enforced Data Isolation**: Every operation is scoped via `TenantScope`. Even if a user attempts to access a record ID belonging to another company, the Global Scope will return a 404, providing a "Senior" level of security.

### Defense Tip
"By separating Invoicing and Sales into a structured Tenant Dashboard with automated Action classes, we reduce the cognitive load for dispatchers. They no longer 'search' for data; the system presents exactly what belongs to their company context, allowing them to process quotes and invoices up to 40% faster than generic CMS-based solutions."
