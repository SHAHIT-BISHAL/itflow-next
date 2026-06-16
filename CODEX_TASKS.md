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

## Handoff / coordination notes

- Work on the `codex/review-tenant-scoping` branch or a child of it; open a PR
  per task so Claude can review before merge.
- Don't edit deployment scripts or the VM — Claude owns deploy
  (`scp overlay/ → /tmp/overlay-upload/ → sudo cp` then `artisan *:clear`).
- Don't touch `Invoice::recalculate()` / `nextNumber()` or the IMAP ingestion
  job logic — flag concerns instead; those need product reasoning.
- After each task: `php artisan test` must pass.
