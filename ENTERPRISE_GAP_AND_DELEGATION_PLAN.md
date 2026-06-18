# ITFlow-Next Enterprise Gap And Delegation Plan

Date: 2026-06-17

Goal: turn ITFlow-Next from a promising Laravel/Livewire MSP app into a serious PSA, service desk, CRM, billing, and documentation platform that can stand beside HaloPSA, IT Glue, Jira Service Management, ServiceNow, Freshservice, HubSpot, and Salesforce.

This is not a clone plan. The winning product shape is a unified MSP operating system centered on the client record: every ticket, asset, password, domain, document, agreement, quote, invoice, project, activity, and automation should compound context instead of living in separate silos.

## Sources Checked

External product references:

- HaloPSA feature overview: https://usehalo.com/halopsa/
- HaloPSA service desk, CRM, contracts, billing, projects, and time tracking pages: https://usehalo.com/halopsa/features/service-desk/
- IT Glue feature page: https://www.itglue.com/features/
- Jira Service Management feature page: https://www.atlassian.com/software/jira/service-management/features
- ServiceNow ITSM page: https://www.servicenow.com/products/itsm.html
- Freshservice feature page: https://www.freshworks.com/freshservice/features/
- HubSpot CRM page: https://www.hubspot.com/products/crm
- Salesforce Sales Cloud page: https://www.salesforce.com/sales/

Internal code references reviewed:

- `overlay/routes/web.php`
- `overlay/routes/console.php`
- `overlay/app/Models/*`
- `overlay/app/Livewire/*`
- `overlay/app/Console/Commands/*`
- `overlay/app/Jobs/*`
- `overlay/database/migrations/*`
- `overlay/database/seeders/*`
- `overlay/tests/Feature/*`
- Existing roadmap/build notes in `README.md`, `PHASE1.md`, `PHASE4.md`, `ENTERPRISE_ROADMAP.md`, and `CODEX_TASKS.md`

## Current Code Reality

The project is an overlay for a generated Laravel 11 app using Livewire 3, Tailwind CSS, Breeze auth, and Spatie permissions. The overlay already contains a meaningful product skeleton:

- Core platform: companies, users, roles, clients, contacts, locations, tags, categories, custom fields, user-client permissions.
- IT documentation: assets, documents, document versions, document relations, domains/certificates, passwords, password reveal access logs.
- Service desk: tickets, replies, attachments table, mail accounts, IMAP polling, inbound email-to-ticket processing, public replies, internal notes, assignment, priority/status.
- CRM: pipelines, stages, deals, deal activity logging, list and kanban views.
- Billing: invoices, invoice items, payments, expenses, recurring invoice models, scheduled recurring invoice generation, invoice PDF route.
- Reporting: overview, revenue, ticket, and expense reports.
- Global search: clients, tickets, invoices, deals.
- Tests: client management, documentation depth, demo seeder, major Livewire modules, tenant scoping around newer modules.

The product is past "prototype shell." It is now a working early PSA seed. The risk is that it can become broad but shallow unless the next phases harden the foundation and connect the modules.

## Biggest Gaps Versus Industry Leaders

### 1. Enterprise Foundation

Current state:

- Multi-company global scope exists through `BelongsToCompany`.
- Some route/model-level tenant checks exist.
- Role seeder only defines broad permissions: users, roles, settings, tags, categories, clients.
- Baseline review found no policies under `overlay/app/Policies`; Codex Sprint 1 has now added the first policy scaffold and Gate mappings.
- Baseline had password reveal audit only; Codex Sprint 1 has added a first global audit logging layer.

Missing:

- Laravel Policies for every major model.
- Per-client access enforcement everywhere, not only company scoping.
- Granular permissions for tickets, billing, docs, passwords, reports, exports, admin settings, and destructive actions.
- Full audit log for create, update, archive, delete, export, login, impersonation, password reveal, invoice send, payment record, and permission changes.
- Company settings for timezone, currency, tax, business hours, numbering, ticket SLA defaults, email defaults, and portal branding.
- 2FA, SSO/OIDC/SAML path, API tokens, webhooks, rate limits, backup/restore, system health.

Why it matters:

Leaders win trust. Without hard security, audit, settings, and access control, the app will feel useful but not safe enough for real MSP operations.

### 2. Service Desk And ITSM

Current state:

- Tickets can be created from web and email.
- Replies support staff/contact authors and internal notes.
- Tickets have basic status, priority, type, source, assignment, resolved/closed timestamps.
- IMAP polling dispatches inbound email jobs.

Missing versus HaloPSA, Jira Service Management, ServiceNow, Freshservice, Zendesk:

- Ticket numbers and configurable ticket numbering.
- Custom ticket statuses, priorities, types, queues, teams, boards, saved views.
- SLA policies, business hours, first response/next response/resolution targets, pause rules, breach escalation.
- Watchers, CC, collaborators, followers, and notification preferences.
- Canned replies, templates, macros, checklists, task lists.
- Merge, split, link, duplicate detection, parent/child tickets.
- Asset linking, affected service linking, related documentation suggestions.
- Time entries on tickets and billable/non-billable classification.
- Problem, change, request, major incident, and approval workflows.
- Self-service portal with forms, service catalog, knowledge suggestions, and ticket status visibility.
- Satisfaction surveys, CSAT/NPS, sentiment, quality review.
- Omnichannel beyond IMAP: web portal, chat, Teams/Slack, inbound API/webhook.
- Agent productivity: keyboard shortcuts, bulk actions, collision detection, activity timeline, AI summaries/draft replies.

Build priority:

1. Ticket number/status/queue/SLA/notification foundation.
2. Ticket time entries and billing bridge.
3. Client portal ticket submission and knowledge suggestions.
4. Problem/change/approval workflows.
5. AI-assisted summaries, suggested replies, and duplicate detection.

### 3. IT Glue-Style Documentation

Current state:

- Assets, documents, document versions, relations to assets/domains/passwords.
- Password vault with encryption and reveal audit.
- Domains and SSL expiry tracking.
- Review due dates and reviewed action.

Missing versus IT Glue:

- Flexible documentation templates with required fields and completion profile.
- Deep relationship graph across configs, locations, contacts, assets, passwords, domains, vendors, contracts, tickets, projects.
- Rich text editor with tables, callouts, code blocks, screenshots, attachments, embeds.
- Document compare/restore from historical versions.
- Immutable audit trail for documentation changes and export/download actions.
- Granular access control and vault-style privileged docs/password areas.
- OTP/TOTP generator, password rotation reminders, password strength/reuse alerts.
- Network documentation: IP ranges, subnets, VLANs, circuits, firewall rules, Wi-Fi networks, ISPs, DNS zones.
- Software/license/SaaS tracking, renewals, seats, compliance, warranty, contract expiry dashboards.
- Checklists/runbooks with task completion and reusable templates.
- Import/export tooling and IT Glue/importer migration path.
- Stale documentation scoring and dashboards by client.

Build priority:

1. Rich editor plus attachments.
2. Template engine and relationship graph.
3. Network/SaaS/license/warranty modules.
4. Documentation quality dashboard and stale-doc workflows.
5. Secure vault permissions, OTP, restore/compare, import/export.

### 4. CRM And Sales

Current state:

- Pipelines, stages, deals, activities, stage movement, won/lost status.
- Basic client/contact association and activity logging.

Missing versus HaloPSA, HubSpot, Salesforce:

- Leads distinct from clients, lead capture, lead source, qualification, conversion to client/contact/deal.
- Tasks and reminders outside deal-only activity.
- Email sync, email templates, sequences, shared inbox, tracking.
- Forecasting by stage probability, owner, close date, weighted pipeline.
- Products/services catalog, quoting, proposal generation, approval, e-signature.
- CPQ basics: bundles, discounts, margins, taxes, optional lines.
- Quote-to-project, quote-to-ticket, quote-to-invoice, quote-to-contract conversion.
- Campaigns, forms, landing capture, attribution, lifecycle stages.
- Duplicate detection, enrichment, dedupe/merge.
- Sales dashboards and activity goals.
- Customer health signals, renewals, upsell opportunities.

Build priority:

1. Leads and lead-to-client conversion.
2. Tasks/reminders and sales dashboard.
3. Product catalog and quote/proposal module.
4. Quote conversion into invoice/project/contract.
5. Forecasting and activity automation.

### 5. Billing, Agreements, And Finance

Current state:

- Manual invoices and invoice items.
- Payments with recalculation.
- Expenses and billable flag.
- Recurring invoices and scheduled generation.
- Invoice PDF route.

Missing versus PSA billing expectations:

- Products/services catalog.
- Tax rates and tax jurisdiction/company defaults.
- Quotes, quote items, approvals, conversion to invoice.
- Contracts/agreements with recurring services, included time, block hours, overage rules, retainer consumption.
- Ticket/project time-to-invoice workflow.
- Expense-to-invoice workflow.
- Credit notes, refunds, deposits, partial allocation, payment reconciliation.
- Aging report and collections workflow.
- Invoice email delivery status, reminders, payment links.
- Accounting integrations: Xero, QuickBooks, Stripe, GoCardless.
- Purchase orders, vendor bills, stock procurement.
- Revenue/margin reports by client, service, agreement, technician.

Build priority:

1. Product/service catalog, tax rates, client billing profile.
2. Quote module.
3. Agreements/contracts and recurring services.
4. Time/expense-to-invoice.
5. Accounting/payment integrations and aging workflow.

### 6. Projects And Work Management

Current state:

- No project module in routes/models.
- Deals exist but cannot become implementation projects.

Missing versus HaloPSA/Freshservice/Jira:

- Projects, phases, milestones, tasks, dependencies, assignments, due dates.
- Project templates for onboarding, migrations, hardware refresh, M365 deployment.
- Budget, estimated hours, actual hours, profitability.
- Project tickets/tasks and project time entries.
- File attachments, notes, internal/client updates.
- Gantt or board/list views.
- Project-to-invoice and project-to-contract links.

Build priority:

1. Projects/tasks/milestones MVP.
2. Project templates and client onboarding project.
3. Project time entries and profitability.
4. Deal/quote-to-project conversion.

### 7. Asset, CMDB, Stock, And Operations

Current state:

- Assets have type, manufacturer, model, serial, IP, MAC, OS, warranty, location, notes.
- Domains/certs track expiry.

Missing versus ITSM/PSA platforms:

- Configuration item classes and custom schemas.
- Asset lifecycle: request, purchase, receive, deploy, maintain, retire.
- Asset ownership/user assignment.
- Relationships/dependency map between assets, services, apps, circuits, docs, tickets.
- Discovery/RMM imports: NinjaOne, Datto RMM, Kaseya, N-able, Intune, Azure, Microsoft 365.
- Stock inventory, warehouses, purchase orders, supplier/vendor management.
- License/SaaS management and seat counts.
- Alerts, service health, status page, on-call, major incidents.

Build priority:

1. Asset relationships and lifecycle statuses.
2. Vendors, products, stock, purchase orders.
3. RMM/import connector framework.
4. Service health and dependency mapping.

### 8. Reporting, Analytics, And AI

Current state:

- Reports are useful but static and aggregate-focused.
- Global search covers four object types.

Missing:

- Saved dashboards and role-based dashboard layouts.
- Scheduled reports.
- Report builder across modules.
- SLA, technician utilization, profitability, customer health, stale docs, renewal, pipeline forecast, aged receivables.
- Full-text search across docs, assets, contacts, tickets, invoices, deals, passwords metadata, domains.
- AI summary/draft/suggest flows for tickets, docs, CRM, and reports.
- Data warehouse/export layer for BI.

Build priority:

1. Full-text search and saved views.
2. SLA/utilization/profitability/doc-health dashboards.
3. Scheduled reports and exports.
4. AI assist features after core workflows are stable.

## Product Principles

1. The client record is the command center.
   Every major object should connect back to client context: service history, docs, assets, passwords, contracts, billing, sales, projects, renewals, and health.

2. Every workflow should end somewhere valuable.
   Ticket work should create time entries and knowledge. Sales should become quotes, projects, contracts, or invoices. Documentation should surface inside tickets. Expiries should create tasks or tickets.

3. Security and audit are first-class features.
   MSP data is sensitive. Passwords, docs, client separation, exports, billing, and permissions need strong controls before broad expansion.

4. Build small vertical slices.
   Avoid building disconnected database tables. Each phase should include schema, policies, UI, tests, seeded demo data, and a visible workflow.

5. Opinionated MSP defaults, configurable enterprise depth.
   Ship with useful defaults for small MSPs, but leave room for queues, workflows, statuses, tax, SLAs, and templates.

## 12-Phase Execution Roadmap

### Phase A: Foundation Hardening

Outcome: safe enough to keep building.

- Add `audit_logs` with polymorphic subject, actor, action, before/after JSON, IP, user agent.
- Add policies for Client, Contact, Location, Asset, Document, Password, Domain, Ticket, Deal, Invoice, Expense, Payment, Pipeline, User.
- Expand permissions by module and action.
- Enforce `User::canAccessClient()` across client-owned screens and nested managers.
- Add company settings table and UI.
- Add numbering settings for tickets, invoices, quotes, projects.
- Add tests for tenant and client access restrictions.

### Phase B: Ticketing Core 2.0

Outcome: real service desk workflows.

- Ticket number, custom statuses, queues, teams.
- SLA policies with business hours.
- Watchers/CC and notification preferences.
- Ticket event timeline.
- Canned replies, templates, macros.
- Attachments UI and storage abstraction.
- Saved views, bulk actions, filters.

### Phase C: Client Portal

Outcome: customers can help themselves.

- Contact login UI using existing `client` guard.
- Portal dashboard, ticket create/view/reply.
- Service catalog forms.
- Knowledge/documentation articles safe for portal.
- Invoice and payment history.
- Portal branding and permissions.

### Phase D: Time Tracking And Billing Bridge

Outcome: tickets become money accurately.

- Time entries on tickets, tasks, projects, and activities.
- Billable flags, rates, roles, approvals.
- Timesheet dashboard.
- Time-to-invoice batch workflow.
- Agreement consumption hooks.

### Phase E: Products, Quotes, And Agreements

Outcome: PSA revenue lifecycle begins.

- Product/service catalog.
- Tax rates.
- Quotes and quote items.
- Quote approval and PDF/email.
- Quote conversion to invoice/project/agreement.
- Agreements/contracts with recurring services, included time, overages.

### Phase F: Documentation 2.0

Outcome: credible IT Glue alternative.

- Rich editor and attachments.
- Document templates and completion profile.
- Relationship graph UI.
- Network documentation tables: IP ranges, VLANs, circuits, firewall rules, Wi-Fi.
- Software/license/SaaS module.
- Review dashboard and stale-doc scoring.
- Version compare/restore.
- OTP/TOTP and privileged vault permissions.

### Phase G: Projects

Outcome: sales and service work can become structured delivery.

- Projects, phases, milestones, tasks.
- Project templates.
- Project time and budget tracking.
- Project files/comments.
- Deal/quote-to-project conversion.
- Project profitability report.

### Phase H: Asset/CMDB And Stock

Outcome: asset context becomes operational.

- CI classes and relationships.
- Asset lifecycle statuses and ownership.
- Vendors/suppliers.
- Stock locations, inventory, purchase orders.
- Warranty/license/contract renewal workflows.
- Import framework for CSV first, RMM/API later.

### Phase I: Automations

Outcome: fewer manual clicks.

- Rule engine with triggers, conditions, actions.
- Ticket assignment/escalation rules.
- SLA breach rules.
- Expiry-to-ticket/task rules.
- Billing reminders.
- Sales follow-up automation.
- Webhooks.

### Phase J: Reporting And Search

Outcome: managers can run the business.

- Full-text search using database full-text first, then Meilisearch/Typesense if needed.
- Saved views and dashboards.
- Scheduled reports.
- SLA, utilization, profitability, aged receivables, pipeline forecast, documentation health.
- Export controls and audit.

### Phase K: Integrations

Outcome: the app fits real MSP stacks.

- Xero/QuickBooks accounting sync.
- Stripe/GoCardless payments.
- Microsoft 365/Graph for contacts, mail, tenants.
- RMM imports: NinjaOne, Datto, Kaseya, N-able, Intune.
- Slack/Teams notifications.
- IT Glue import path.
- Halo/Autotask/ConnectWise migration importers.

### Phase L: AI And Assistant Layer

Outcome: product feels modern after the core workflows are trustworthy.

- Ticket summary and suggested reply.
- Ticket categorization and priority suggestions.
- Documentation suggestions while replying.
- Draft KB/runbook from resolved ticket.
- Duplicate ticket detection.
- CRM next-best action.
- Search assistant over client context.
- Report insights.

## Delegation Plan: Codex And Claude Code

### Operating Model

- Work in short vertical phases with one shared acceptance checklist.
- Each phase has one architecture owner and one implementation/testing owner.
- Prefer separate branches per slice; merge only when tests pass and the UI workflow is verified.
- Keep schema, model, policy, UI, tests, seed data, and docs together for each feature.
- Maintain `CODEX_TASKS.md` as the tactical queue and this document as the strategic map.

### Codex Owns

Best work for Codex:

- Repository inventory and impact analysis.
- Mechanical implementation once the product shape is clear.
- Migrations, models, relationships, factories, seeders.
- Policies and permission wiring.
- Feature tests, tenant/client scoping tests, regression tests.
- Static analysis, type/PHPDoc cleanup, test failure triage.
- Repetitive Livewire CRUD and Blade component normalization.
- Import/export plumbing.
- Refactors with measurable behavior preservation.

Immediate Codex queue:

1. Foundation audit log tables, model, trait/service, and tests.
2. Policy scaffold for all major models.
3. Permission expansion in `RolePermissionSeeder`.
4. Client access enforcement tests for nested managers.
5. Ticket numbering and configurable invoice/ticket prefixes.
6. Ticket event timeline schema and tests.
7. Time entry schema/factories/tests.
8. Product/service catalog schema and simple CRUD.

### Claude Code Owns

Best work for Claude Code:

- Product architecture and user workflow design.
- UX of major flows: service desk workspace, client command center, portal, quote builder, documentation graph.
- Feature acceptance criteria and copy.
- Reviewing security-sensitive design decisions.
- Integrations architecture and API credential UX.
- AI workflow prompt/product design.
- Deployment sequencing and production verification.
- Cross-module prioritization when tradeoffs are needed.

Immediate Claude Code queue:

1. Define target UX for the client command center.
2. Design service desk workspace: queues, statuses, SLA states, timeline, reply composer.
3. Specify permission matrix for admin, technician, billing, sales, read-only, and client portal users.
4. Design company settings and numbering settings screens.
5. Define agreement/contract billing rules.
6. Design documentation template and relationship graph UX.
7. Define quote-to-project/invoice/agreement workflow.
8. Review Codex foundation PRs for product and security fit.

### Side-By-Side Sprint 1: Trust Foundation

Codex:

- Add audit logging infrastructure.
- Add policies and granular permissions.
- Add tests around company and client restrictions.
- Add company settings and numbering settings schema.

Claude Code:

- Produce permission matrix.
- Review audit event taxonomy.
- Design settings UI and client command center shape.
- Validate that foundation decisions support portal and integrations.

Done when:

- Every major route is protected by auth, permission, policy, and tenant/client checks.
- Sensitive actions emit audit logs.
- Tests prove company A cannot view or mutate company B data.
- User-client restrictions actually restrict nested tabs and global pages.

### Codex Sprint 1 Checkpoint

Completed on 2026-06-17:

- Added audit log migration/model/service with actor, subject, before/after, metadata, IP, and user agent.
- Added read-only Admin Audit Logs screen protected by `view audit logs`.
- Added policy scaffold and explicit Gate mappings for major tenant-owned models.
- Expanded module permissions and role defaults for admin, technician, billing, sales, and read-only users.
- Split route/sidebar access by module permission instead of relying on broad admin access.
- Hardened `User::canAccessClient()` and `Client::visibleTo()` so client restrictions also enforce company ownership.
- Applied client-access checks across client nested managers, global asset/domain pages, tickets, deals, invoices, expenses, reports, dashboard, global search, and invoice PDFs.
- Added audit events for client, documentation, password access, ticket, deal, invoice/payment, asset, domain, and expense workflows.
- Added/expanded tests for restricted-client visibility, audit creation, invoice/payment/ticket/deal actions, and the audit log admin page.

Local verification:

- `git diff --check` passes, with only expected CRLF warnings from the Windows working tree.
- PHP is not installed in the local Windows shell, so Laravel/PHPUnit tests still need to run in the PHP-capable environment before merge/deploy.

Next Codex slice:

1. Add company settings and numbering settings schema/UI.
2. Add ticket numbers and configurable ticket/invoice/quote/project prefixes.
3. Add ticket event timeline schema and automatic event writer.
4. Add export/download audit events and permission gates.

Next Claude Code slice:

1. Review and refine the permission matrix by role.
2. Review the audit event taxonomy and name conventions.
3. Design company settings and numbering settings UX.
4. Define the client command center information architecture.

### Side-By-Side Sprint 2: Service Desk That Feels Real

Codex:

- Add ticket numbers, statuses, queues, teams.
- Add SLA policy tables and calculations.
- Add ticket watchers/CC.
- Add ticket event timeline.
- Add saved views and filters.

Claude Code:

- Design technician ticket workspace.
- Define default MSP ticket statuses/queues/SLA rules.
- Review email threading behavior and notification copy.
- Design portal ticket intake form.

Done when:

- A technician can work from a queue, see SLA risk, reply, add internal notes, change status, attach files, and see timeline/history.
- A ticket can be searched by number and linked to client/assets/docs.
- Notifications are predictable and tested.

### Side-By-Side Sprint 3: Money Loop

Codex:

- Add time entries on tickets.
- Add product/service catalog and tax rates.
- Add quote tables and tests.
- Add time/expense-to-invoice batch flow.

Claude Code:

- Design time entry UX.
- Design quote builder and approval flow.
- Define contract/agreement billing scenarios.
- Review invoice/payment/accounting sync assumptions.

Done when:

- A ticket can collect billable time, approve it, and move it to an invoice.
- A quote can become an invoice or project.
- Recurring services can be modeled without abusing invoice templates.

### Side-By-Side Sprint 4: Documentation Differentiator

Codex:

- Add editor/attachment plumbing.
- Add document templates and required fields.
- Add network docs tables.
- Add version restore/compare backend.
- Add stale documentation dashboard.

Claude Code:

- Design documentation workspace and graph UX.
- Define MSP template library.
- Define documentation trust score/completion profile.
- Review privileged vault and OTP UX.

Done when:

- A technician opening a ticket can see related docs, assets, credentials, domains, and stale-doc warnings.
- Documentation has templates, review dates, version safety, and audit trails.

## First Ten Build Tickets

1. Done in Codex Sprint 1: create `audit_logs` migration/model/service and wire password/document/ticket/invoice actions.
2. Done in Codex Sprint 1: create module-level permissions and update roles.
3. Done in Codex Sprint 1: add policies for all major models.
4. Done in Codex Sprint 1: fix client access enforcement in nested managers and global asset/domain pages.
5. Next: add company settings and numbering settings.
6. Add ticket number column and generator.
7. Add ticket event timeline.
8. Add ticket watchers and notification preferences.
9. Add `time_entries` linked to tickets, projects, clients, invoices.
10. Add product/service catalog and tax rates.

## North Star

The project becomes special when a technician can open one client or ticket and immediately see:

- Who the client is and whether they are healthy.
- What is broken, who owns it, and when the SLA expires.
- Which assets, services, passwords, domains, warranties, contracts, and docs matter.
- What was tried before.
- Whether the work is billable.
- Which quote, agreement, project, or invoice this work connects to.
- What the next best action is.

That is the path to making this the best project of your life: not by adding every feature at once, but by making every feature feed the next workflow.

## Reality-Check Addendum (2026-06-18, Claude Code)

Review of this plan against the *actually deployed* VM surfaced gaps that the
strategic roadmap does not account for. These are correctness/ops issues that
must be closed before — and alongside — feature phases, because the plan's own
vertical-slice principle (schema + policies + UI + tests + deploy) was not being
enforced end-to-end.

### Findings

1. **Migrations shipped ahead of schema.** The Sprint 1 `audit_logs` migration
   was committed and the audit-writing code deployed, but the migration had not
   run on the VM (the table was missing). Any audited action would 500 in prod.
   *Fixed this session:* re-synced migrations, ran `migrate --force`, re-seeded
   `RolePermissionSeeder`, verified `audit_logs` exists and the admin holds the
   30 new module permissions including `view audit logs`.

2. **The scheduler is not running in production.** No root/www-data crontab and
   no systemd timer invokes `artisan schedule:run`. Therefore `mail:poll`
   (email-to-ticket) and `invoices:generate-recurring` have never fired in prod.
   Two "shipped" features are inert. *Needs a one-time ops change* (cron entry).

3. **Tests have never executed in a PHP environment.** Sprint 1 "Done when"
   criteria require passing tenant/client-isolation tests, but PHP isn't on the
   author's Windows shell and the VM has no isolated test database. A bare
   `php artisan test` against the live DB already wiped demo data once. The
   `itflow_next_testing` database + grant is the critical unblock for CI.

### Process improvements (adopt before next sprint)

- **Deploy gate, not just a test gate.** Each sprint's "Done when" must include:
  migration ran on target, post-deploy smoke of one authenticated page per
  touched module, and the scheduler/queue path exercised if the slice adds a job.
- **No code merges ahead of its migration.** Treat "migration deployed + run" as
  part of the same slice; never push code referencing a table/column that isn't
  live.
- **Stand up the test DB first.** `itflow_next_testing` + a CI step running
  `php artisan test` is Phase-A prerequisite #0; the isolation tests the plan
  leans on are worthless until they actually run.
- **Use real review branches.** Codex currently commits straight to the shared
  `codex/review-tenant-scoping` branch that Claude deploys from — that is how the
  `audit_logs` gap reached prod unreviewed. One feature branch + PR per slice.
- **Weight the old-ITFlow data import higher.** The original Phase 6 goal (import
  from the existing ITFlow DB) is buried under "Integrations." For this user it is
  likely higher value than several greenfield modules and should be sequenced
  earlier.

### Revised immediate critical path (supersedes "next slice" ordering)

0. Stand up `itflow_next_testing` + run the suite green in the PHP environment.
1. Install the production scheduler cron (unblocks mail + recurring billing).
2. Company settings + numbering settings (dependency for SLA, tax, billing).
3. Ticket numbering + event timeline.
4. Time entries + product/tax catalog (the billing money-loop foundation).

