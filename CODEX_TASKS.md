# Codex Delegation — ITFlow-Next

Tasks scoped for Codex to run in parallel. These are the kinds of work Codex is
strong at: mechanical, well-specified, high-volume edits and test/coverage work
that don't need product/design judgment. Claude is keeping the UX/design,
architecture, and deployment decisions.

> Repo: `SHAHIT-BISHAL/itflow-next` · App code lives under `overlay/` and is
> deployed onto a stock Laravel 11 + Livewire 3 + Tailwind app at
> `/var/www/itflow-next`. Branch in play: `codex/review-tenant-scoping`.

---

## Why these go to Codex (and not Claude)

Codex is well-suited to **deterministic, spec-driven, repeat-the-pattern** work
where the "right answer" is verifiable by tests or a type checker, and where the
main cost is volume rather than judgment. Claude is holding the items that need
design taste, cross-module product reasoning, or careful deploy steps.

---

## Task 1 — Feature test coverage for all Livewire components  ⬅ best fit

There are currently **no feature tests** for any of the Phase 3–6 Livewire
components. Write Pest/PHPUnit feature tests under `overlay/tests/Feature/`.

For each component below, assert: (a) the page renders for an authenticated
user, (b) tenant scoping — a user from company A cannot see/load company B's
records (this is the existing branch's whole theme), (c) the primary write
action persists correctly and re-runs `recalculate()` where relevant.

Components to cover:
- `Invoices\Index`, `Invoices\Create`, `Invoices\Show` (incl. `recordPayment`)
- `Expenses\Index`
- `Deals\Index` (list + kanban), `Deals\Show` (activity logging)
- `Tickets\Index`, `Tickets\Show` (`sendReply`, `updateMeta`)
- `Reports\Overview|Revenue|Tickets|Expenses` (render + correct aggregates)
- `GlobalSearch` (results respect company scope; <2 chars returns nothing)

Use existing factories where present; create missing factories under
`database/factories/`. Target: every component has at least a render + a
tenant-isolation test. Run `php artisan test` green before finishing.

## Task 2 — Model factories + a seeder smoke test

Audit `overlay/app/Models/` and create a `*Factory` for every model that lacks
one (Invoice, InvoiceItem, Payment, Expense, RecurringInvoice,
RecurringInvoiceItem, Ticket, TicketReply, Deal, Pipeline, Activity,
MailAccount, etc.). Then add a single test that runs
`DemoDataSeeder` end-to-end and asserts no exceptions + expected row counts.

## Task 3 — PHPDoc + property typing pass on models

Add `@property` / `@property-read` PHPDoc blocks to every Eloquent model in
`overlay/app/Models/` reflecting columns (from migrations) and relationships.
Add return types to relationship methods (`: HasMany`, `: BelongsTo`, etc.).
Pure mechanical — no behavior change. Don't touch `recalculate()` or
`nextNumber()` logic.

## Task 4 — Static analysis baseline (Larastan/PHPStan level 5)

Add `larastan/larastan` to `require-dev`, a `phpstan.neon` configured to level 5
scanning `overlay/app`, and fix the trivially-fixable findings (missing return
types, undefined-variable false-positives via correct typehints). Leave a
`// @phpstan-ignore` with a comment for anything that needs Claude's judgment,
and list those in a `PHPSTAN_TODO.md` for review.

## Task 5 — Normalize Tailwind class usage into Blade components

Mechanical refactor: the same long Tailwind strings repeat across views
(buttons, table headers, the `px-3 py-2 rounded-lg ... hover:bg-slate-800` nav
links). Extract recurring patterns into `x-ui.*` components
(`x-ui.button`, `x-ui.table`, `x-ui.th`, `x-ui.nav-link`) and replace usages.
Keep the rendered output byte-for-byte equivalent (diff the compiled HTML on a
couple of pages to confirm). This is find-and-replace heavy → ideal for Codex.

---

---

## Status (2026-06-18)

Tasks 1–5 **DONE** and merged (feature tests, factories+seeder smoke, model
PHPDoc, PHPStan baseline, Tailwind→component normalization). Foundation Sprint 1–2
(audit logs, policies, permissions, company/numbering settings, ticket numbering +
event timeline) also landed and is deployed. Suite is green (41+ passing) against
the isolated `itflow_next_testing` DB.

Claude is currently building the **Time Entries → billing bridge** slice
(`time_entries` table/model, ticket-workspace time logging UI, tests). The Sprint 3
queue below is the parallel mechanical work that compounds toward a full PSA + IT
documentation system. **Coordinate on `time_entries`:** Claude owns its schema and
the ticket-side UI; Codex owns the time-to-invoice batch flow that consumes it
(Task S3-2).

---

## Sprint 3 — PSA money-loop + IT-doc depth (Codex queue)

### S3-1 — Product/Service catalog + tax rates
Schema + models + factories + admin CRUD + tests:
- `products` (company_id, name, sku, type [product|service|recurring], description,
  default_price, default_cost, tax_rate_id, is_active).
- `tax_rates` (company_id, name, rate, is_default).
- Wire an optional `product_id` onto `invoice_items` (nullable FK, additive
  migration) so invoice lines can reference a catalog product without breaking the
  existing free-text path.
- Tenant-scoping + policy tests. Don't alter `Invoice::recalculate()`/`nextNumber()`.

### S3-2 — Time-to-invoice batch flow
Depends on Claude's `time_entries` table (billable, uninvoiced scopes already
defined). Build:
- A `BillTimeEntries` service/action: given a client + date range, group uninvoiced
  billable entries, create a draft invoice with one line per entry (or grouped),
  stamp `invoice_id`/`invoiced_at` on the consumed entries inside a transaction.
- A Livewire screen under Billing ("Unbilled time" → select → create invoice).
- Tests: entries get stamped, totals match, re-running doesn't double-bill.

### S3-3 — SLA policies + business hours
- `sla_policies` (company_id, name, priority, first_response_minutes,
  resolution_minutes), `business_hours` (company_id, weekday, open, close).
- A service that computes `sla_due_at` on ticket create from policy + business hours
  (the column already exists on `tickets`). Factories + tests for the calculator
  (skip weekends/off-hours).

### S3-4 — Projects / tasks / milestones (schema MVP)
- `projects` (company_id, client_id, name, status, budget_hours, starts/ends),
  `project_tasks` (project_id, title, status, assignee, due, estimated/actual mins),
  `project_milestones`. Models, factories, tenant scoping, render-only Livewire list.
  UI depth comes later — Codex lands the spine + tests.

### S3-5 — Network documentation tables (IT Glue depth)
- `networks` (client_id, name, subnet/CIDR, vlan, gateway, dns, notes),
  `ip_addresses` (network_id, address, status, asset_id nullable, label).
  Models, factories, relationship to assets, tests. List UI under a client's
  Documentation tab.

### S3-6 — Document rich-text + attachments plumbing
- Storage abstraction for document/ticket attachments (the `ticket_attachments`
  table exists but has no upload path). Add a polymorphic `attachments` table +
  `HasAttachments` trait + a Livewire file-upload component. Mechanical/reusable;
  Claude designs the editor UX on top.

Acceptance for every S3 task: migration + model + factory + tenant-scope test +
green `php artisan test`; open one branch + PR per task.

---

## Handoff / coordination notes

- Work on the `codex/review-tenant-scoping` branch or a child of it; open a PR
  per task so Claude can review before merge.
- Don't edit deployment scripts or the VM — Claude owns deploy
  (`scp overlay/ → /tmp/overlay-upload/ → sudo cp` then `artisan *:clear`).
- Don't touch `Invoice::recalculate()` / `nextNumber()` or the IMAP ingestion
  job logic — flag concerns instead; those need product reasoning.
- After each task: `php artisan test` must pass.
