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
