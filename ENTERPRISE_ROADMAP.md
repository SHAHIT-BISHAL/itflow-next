# ITFlow-Next Enterprise Roadmap

This document captures improvement suggestions for growing ITFlow-Next into a full enterprise MSP platform with ticketing, IT Glue-style documentation, Xero-like billing, and Salesforce-like sales capabilities.

## Product Direction

ITFlow-Next should not become four disconnected applications. The strongest path is one shared enterprise platform built around a central client record.

The core platform objects should be:

- Company
- Client
- Contact
- Location
- Asset
- Document
- Password
- Domain
- Ticket
- Opportunity
- Quote
- Invoice

The client record should become the main operational hub where staff can see service history, documentation, credentials, assets, domains, sales activity, contracts, invoices, and open work.

## Immediate Platform Improvements

The current app already has a useful foundation: clients, contacts, locations, assets, documents, passwords, domains, roles, categories, tags, and multi-company scoping. Before adding large modules, strengthen the enterprise base.

- Add Laravel Policies for every major model instead of relying mostly on route middleware.
- Enforce `User::canAccessClient()` everywhere, especially client detail, assets, documents, passwords, and domains.
- Split admin permissions in `overlay/routes/web.php` instead of gating all admin routes with `manage users`.
- Add audit logs for create, update, archive, delete, view-password, copy-password, export, and impersonation actions.
- Use real soft deletes or a consistent archive service across all models.
- Add global search across clients, contacts, assets, documents, domains, tickets, invoices, and opportunities.
- Add an activity timeline per client.
- Add notification support through email, database notifications, and browser alerts.
- Add import/export for clients, contacts, assets, domains, and documents.
- Add stronger indexes and uniqueness constraints for domains, serial numbers, contact emails, invoice numbers, ticket numbers, and quote numbers.
- Add company-level settings for timezone, currency, tax defaults, business hours, ticket numbering, invoice numbering, and SLA defaults.

## Ticketing Module

Ticketing should probably be the next major module because it connects clients, contacts, assets, documentation, time entries, billing, and client portal workflows.

### Core Features

- Tickets with number, subject, description, status, priority, source, category, SLA, due date, and resolution.
- Ticket statuses such as New, Open, Pending Client, Waiting Vendor, Resolved, and Closed.
- Assignment to users and teams.
- Public replies, private internal notes, and system events.
- File attachments.
- Email-to-ticket ingestion using the existing queue worker direction.
- Ticket watchers and CC contacts.
- SLA policies by client, priority, contract, and business hours.
- Time entries on tickets.
- Canned replies and ticket templates.
- Merge, split, link, and duplicate detection.
- Client portal ticket submission.
- Automation rules for assigning, tagging, prioritizing, escalating, and closing tickets.

### Enterprise Features

- Queues or boards per team.
- Escalation policies.
- Approval workflows.
- Parent and child tickets.
- Problem/change management.
- Ticket satisfaction surveys.
- Technician workload dashboard.
- Saved views and filters.
- Bulk actions.
- Knowledge base suggestions while replying to tickets.

### Suggested Tables

- `tickets`
- `ticket_comments`
- `ticket_attachments`
- `ticket_watchers`
- `ticket_statuses`
- `ticket_priorities`
- `ticket_slas`
- `ticket_time_entries`
- `ticket_automations`
- `ticket_links`
- `ticket_events`

## IT Glue-Style Documentation

The app already has early `Asset`, `Document`, `Password`, and `Domain` models. To feel like IT Glue, documentation needs relationships, templates, versioning, strong search, and trust signals.

### Core Features

- Flexible document templates.
- Document version history.
- Related items linking documents to assets, passwords, contacts, domains, tickets, and clients.
- Rich text editor with tables, callouts, code blocks, screenshots, and attachments.
- Runbooks and SOPs.
- Network documentation for IP ranges, VLANs, firewall rules, circuits, ISPs, and Wi-Fi networks.
- Software and license tracking.
- Expiration tracking for warranties, SSL certificates, domains, licenses, and contracts.
- Password vault with reveal/copy audit logging.
- OTP/TOTP support for password records.
- Document approval and review dates.
- Stale documentation reports.
- Client-specific documentation dashboard.

### Security Requirements

- Every password reveal and copy action must be audit logged.
- Password records should have granular permissions.
- Sensitive fields should be hidden by default.
- Consider per-record access controls for privileged documentation.
- Add export controls and audit logs.

### Suggested Tables

- `document_versions`
- `document_relations`
- `document_attachments`
- `network_ranges`
- `vlans`
- `circuits`
- `software_licenses`
- `password_access_logs`
- `documentation_reviews`

## Billing And Accounting Module

Full accounting is a large domain. For an MSP platform, build PSA-style billing first, then integrate with Xero or QuickBooks later. Trying to clone all of Xero too early will slow the product down.

### Billing MVP

- Products and services catalog.
- Tax rates.
- Quotes.
- Quote line items.
- Invoices.
- Invoice line items.
- Payments.
- Credit notes.
- Recurring invoices.
- Client billing profiles.
- Payment terms.
- Time-to-invoice from ticket time entries.
- Expenses and billable expenses.
- PDF invoice generation.
- Email invoice delivery.
- Basic aging report.

### Later Accounting Features

- Chart of accounts.
- Bank reconciliation.
- Journal entries.
- Purchase orders.
- Vendor bills.
- Financial reports.
- Xero and QuickBooks integrations.

Payroll should stay out of scope unless the product intentionally becomes a full accounting suite.

### Suggested Tables

- `products`
- `tax_rates`
- `quotes`
- `quote_items`
- `invoices`
- `invoice_items`
- `payments`
- `credit_notes`
- `recurring_invoice_profiles`
- `expenses`
- `contracts`
- `contract_items`

## Salesforce-Like Sales Module

The sales module should focus on pipeline visibility and conversion into clients, quotes, projects, tickets, and invoices.

### Core Features

- Leads.
- Accounts/clients.
- Contacts.
- Opportunities.
- Pipeline stages.
- Activities such as calls, emails, meetings, and tasks.
- Proposals and quotes.
- Product/service catalog integration.
- Forecasting.
- Lead source tracking.
- Win/loss reasons.
- Sales dashboard.
- Follow-up reminders.
- Email templates.
- Convert lead to client.
- Convert quote to invoice or project.

### Suggested Tables

- `leads`
- `opportunities`
- `opportunity_stages`
- `activities`
- `tasks`
- `proposals`
- `campaigns`
- `lead_sources`

## Enterprise Foundation

These features make the product feel serious and scalable rather than just feature-rich.

- Audit log everywhere.
- Granular RBAC and eventually teams/departments.
- Multi-company isolation tests.
- Client portal with the separate `client` guard.
- API tokens and webhooks.
- Background jobs for mail ingestion, notifications, reminders, invoice sending, and expiry checks.
- Full-text search, eventually using Meilisearch or Typesense.
- File storage abstraction for local and S3-compatible storage.
- Backup and restore tooling.
- System health page.
- Workflow automation engine.
- Saved reports and dashboards.
- Scheduled reports.
- Data retention policies.
- Field-level encryption where needed.
- Rate limiting for sensitive actions.
- Two-factor authentication for staff users.
- Optional SSO/SAML/OIDC for larger customers.

## Recommended Roadmap

### Phase 2: Harden The Foundation

- Policies for all major models.
- Enforced client access restrictions.
- Granular admin permissions.
- Audit logs.
- Global search.
- Client activity timeline.
- Better settings screens.
- More tenancy tests.

### Phase 3: Ticketing MVP

- Tickets.
- Ticket comments.
- Internal notes.
- Assignments.
- Statuses and priorities.
- Email-to-ticket.
- Ticket notifications.
- Basic client portal ticket submission.

### Phase 4: Documentation Depth

- Versioned documents.
- Asset relationships.
- Password audit logs.
- Runbooks.
- Network documentation.
- Expiration dashboards.
- Stale documentation review workflow.

### Phase 5: Billing

- Products and services.
- Quotes.
- Invoices.
- Payments.
- Recurring billing.
- Time-to-invoice from tickets.
- Basic financial reports.

### Phase 6: Sales CRM

- Leads.
- Opportunities.
- Activities.
- Pipeline.
- Quote conversion.
- Sales dashboards.

### Phase 7: Automations And Reporting

- SLA automation.
- Workflow rules.
- Scheduled jobs.
- Saved dashboards.
- Scheduled reports.
- Webhooks and API integrations.

## Product Experience Goal

The strongest user experience is not just having many modules. It is making the client record the place where everything comes together.

When a ticket comes in, a technician should instantly see:

- Client details
- Primary contacts
- Related assets
- Passwords they are allowed to access
- Domains and SSL expiry
- Relevant documents and runbooks
- Previous tickets
- Contract/SLA context
- Billing status
- Open opportunities or onboarding notes

That unified workflow is where ITFlow-Next can feel enterprise-grade.
