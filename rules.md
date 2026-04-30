# Project Rules: Invoicing/Webshop CMS (Laravel 13 + Livewire 4)

## Project Context
This project is a B2B SaaS and CRM for the Logistics sector named Master-Digit. It is NOT a dynamic retail shop. The primary focus is Lead Capture (from external transport forms) and Invoice/Quote management for logistics companies.

## Core Tech Stack
- Framework: Laravel 13 (Latest Conventions)
- Frontend: Livewire 4 (Single File Components - SFC)
- UI Library: Flux UI (Free Tier)
- Database: MySQL / MariaDB
- Testing: Pest
- Starter Kit: Official Laravel Livewire Starter Kit

## 1. Learning & Accountability (CRITICAL)
- After EVERY task, update `DEV_JOURNAL.md` with:
    - Task Summary: What was built.
    - Design Patterns: Why we used Actions/Services/Enums.
    - Presentation Tip: A 1-sentence explanation of this code for a technical examiner.
- Use verbose DocBlocks for complex logic to help the user learn.

## 2. Architectural Standards (Senior Level)
- **Separation of Concerns:**
    - **Routes:** Endpoint definition and Middleware only.
    - **Controllers:** (If used) Minimal. Receive request -> Call Action -> Return Response.
    - **Livewire Components:** Handle UI state and user input only. NO heavy business logic.
    - **Actions:** Pure business logic (e.g., `CreateInvoiceAction`, `ProcessPaymentAction`). One public `handle()` method.
    - **Services:** External integrations (Stripe, Mail, File Storage).
    - **Models:** Thin models. Relationships, Scopes, Casts, and Accessors only.
- **Data Integrity:**
    - Use **Enums** for all statuses (e.g., `InvoiceStatus`, `UserRole`).
    - Use **Decimal(10,2)** for all currency. NEVER use `float`.
    - Always use **Foreign Key Constraints** and **Soft Deletes** for business-critical data.

## 3. Livewire 4 Implementation Rules
- Default to **Single File Components (SFC)**.
- Use `#[Computed]` for all data-fetching to ensure caching within the request.
- Use `#[Validate]` or **Form Objects** for input handling.
- Use `Route::livewire()` for full-page components.
- Keep the `render()` method clean. Logic should reside in lifecycle hooks or computed properties.

## 4. Specific Business Logic (Invoicing/Shop)
- **Stripe Integration:**
    - Must reside in a `StripeService`.
    - Status updates must be triggered by Webhooks, not just URL redirects.
    - Orders are created as `pending` and only moved to `paid` after API verification.

## 5. Security & Patterns
- **Policies:** Every model must have a Policy (e.g., `InvoicePolicy`) for authorization.
- **Middleware:** Use a dedicated `EnsureUserIsAdmin` middleware for the backend.
- **Naming:** Follow strict English naming conventions. Avoid abbreviations.
- **Eager Loading:** Always prevent N+1 queries by eager loading relationships in components.

## 6. Prohibited Patterns (NEVER DO)
- NO business logic in Blade views or Routes.
- NO hardcoded IDs or Secrets (use `.env`).
- NO Livewire 2/3 legacy syntax.
- NO Filament/Jetstream/Inertia (unless specifically requested later).
- NO inline queries in the `render()` method.
- NO Tutorial-style hacks. Code must be "Production Grade."

## 7. Development Process
- Work step-by-step.
- Before a major change, list the files that will be affected.
- If multiple solutions exist, choose the most "Senior/Scalable" approach.
- Every feature must be demo-ready after `php artisan migrate:fresh --seed`.

## 8. Mandatory Task Lifecycle & Documentation
EVERY TIME you (the AI) execute a task or change code, you MUST follow this sequence:
1. **Analyze:** Check `rules.md` to ensure compliance with Laravel 13 + Livewire 4 standards.
2. **Execute:** Perform the code changes.
3. **Log:** Append an entry to `DEV_JOURNAL.md` (create it if not exists). 
    - The entry MUST use the following template:

    ### [Date/Time] - Task: [Short Task Name]
    - **Files Modified:** List of files.
    - **Technical Logic:** Explain the logic used (e.g., Why this Action was created? Why this specific Hook was used?).
    - **Senior Concept:** Explain the architectural pattern (e.g., Dependency Injection, DTO, Repository-ish pattern).
    - **Exam Defense Tip:** A 1-2 sentence explanation I can use during my presentation to prove I understand this part.
    - **Keywords to Learn:** List 3 technical terms used in this task.