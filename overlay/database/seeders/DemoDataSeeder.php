<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Domain;
use App\Models\Location;
use App\Models\Password;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(['name' => 'My MSP'], [
            'email' => 'hello@my-msp.test',
            'currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@itflow-next.test'],
            [
                'company_id' => $company->id,
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('Administrator')) {
            $admin->assignRole('Administrator');
        }

        if (Client::count() > 0) {
            return;
        }

        $client = Client::create([
            'company_id' => $company->id,
            'name' => 'Acme Corporation',
            'type' => 'Customer',
            'website' => 'https://acme.example.com',
            'net_terms' => 30,
            'notes' => 'Demo client created by seeder.',
        ]);

        $location = Location::create([
            'client_id' => $client->id,
            'name' => 'Head Office',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62701',
            'phone' => '555-0100',
            'is_primary' => true,
        ]);

        Contact::create([
            'client_id' => $client->id,
            'location_id' => $location->id,
            'name' => 'Jane Doe',
            'title' => 'Office Manager',
            'email' => 'jane@acme.example.com',
            'phone' => '555-0101',
            'is_primary' => true,
            'is_billing' => true,
        ]);

        // Phase 2 — IT Documentation demo data
        $server = Asset::create([
            'company_id'   => $company->id,
            'client_id'    => $client->id,
            'location_id'  => $location->id,
            'name'         => 'ACME-DC01',
            'asset_type'   => 'Hardware',
            'manufacturer' => 'Dell',
            'model'        => 'PowerEdge R740',
            'serial_number' => 'SN-DCO1-1234',
            'ip_address'   => '192.168.1.10',
            'os'           => 'Windows Server',
            'os_version'   => '2022',
            'purchased_at' => '2022-06-01',
            'warranty_expires_at' => now()->addDays(45)->toDateString(),
            'notes'        => 'Primary domain controller.',
        ]);

        Asset::create([
            'company_id'   => $company->id,
            'client_id'    => $client->id,
            'name'         => 'Microsoft 365 E3',
            'asset_type'   => 'Software',
            'manufacturer' => 'Microsoft',
            'notes'        => '25 seats. Renewal due annually.',
        ]);

        Document::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'created_by' => $admin->id,
            'title'      => 'Network Overview',
            'content'    => "# Acme Corporation — Network Overview\n\nSubnet: 192.168.1.0/24\nGateway: 192.168.1.1\nDNS: 192.168.1.10 (internal), 8.8.8.8 (external)\n\n## VLANs\n- VLAN 10 — Servers\n- VLAN 20 — Workstations\n- VLAN 30 — Guest WiFi\n\n## Key Servers\n- ACME-DC01 (192.168.1.10) — Domain Controller / DNS\n- ACME-FS01 (192.168.1.11) — File Server",
        ]);

        Document::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'created_by' => $admin->id,
            'title'      => 'Backup Procedures',
            'content'    => "# Backup Procedures\n\nDaily backups run at 02:00 via Veeam.\nRetention: 30 daily, 12 monthly.\n\nOffsite replication to Azure Blob Storage — container: acme-backups.\n\nTest restores performed quarterly.",
        ]);

        Password::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'name'       => 'Domain Admin',
            'username'   => 'administrator@acme.local',
            'password'   => 'SuperSecret123!',
            'notes'      => 'Main domain admin — rotate every 90 days.',
        ]);

        Password::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'name'       => 'Office 365 Global Admin',
            'username'   => 'admin@acme.example.com',
            'password'   => 'AcmeM365Admin!',
            'url'        => 'https://portal.office.com',
            'notes'      => 'Shared M365 global admin account.',
        ]);

        $domain = Domain::create([
            'company_id'    => $company->id,
            'client_id'     => $client->id,
            'name'          => 'acme.example.com',
            'registrar'     => 'Namecheap',
            'expires_at'    => now()->addDays(20)->toDateString(),
            'auto_renew'    => false,
            'dns_provider'  => 'Cloudflare',
            'ssl_expires_at' => now()->addDays(20)->toDateString(),
            'ssl_issuer'    => "Let's Encrypt",
            'notes'         => 'Main company domain — RENEW SOON.',
        ]);

        Domain::create([
            'company_id'    => $company->id,
            'client_id'     => $client->id,
            'name'          => 'acme-old.example.com',
            'registrar'     => 'GoDaddy',
            'expires_at'    => now()->addDays(180)->toDateString(),
            'auto_renew'    => true,
            'dns_provider'  => 'GoDaddy',
            'ssl_expires_at' => now()->addDays(90)->toDateString(),
            'ssl_issuer'    => 'DigiCert',
        ]);

        Document::where('client_id', $client->id)->each(function (Document $document) use ($admin, $server, $domain) {
            $document->update([
                'document_type' => str($document->title)->contains('Network') ? 'network' : 'runbook',
                'review_due_at' => now()->addMonths(3)->toDateString(),
            ]);

            $document->versions()->create([
                'created_by' => $admin->id,
                'version_number' => 1,
                'title' => $document->title,
                'content' => $document->content,
                'change_summary' => 'Initial seeded version',
            ]);

            if (str($document->title)->contains('Network')) {
                $document->relations()->create([
                    'related_type' => Asset::class,
                    'related_id' => $server->id,
                    'relationship_type' => 'reference',
                ]);
                $document->relations()->create([
                    'related_type' => Domain::class,
                    'related_id' => $domain->id,
                    'relationship_type' => 'reference',
                ]);
            }
        });

        Client::create([
            'company_id' => $company->id,
            'name' => 'Globex Industries',
            'type' => 'Lead',
            'is_lead' => true,
            'net_terms' => 15,
        ]);

        // Phase 3 — Ticket demo data
        $contact = $client->contacts()->first();

        $t1 = Ticket::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'contact_id' => $contact?->id,
            'assigned_to' => $admin->id,
            'subject'    => 'DC01 running out of disk space',
            'status'     => 'open',
            'priority'   => 'high',
            'type'       => 'technical',
            'source'     => 'email',
        ]);
        $t1->replies()->create([
            'contact_id' => $contact?->id,
            'body'       => "Hi team,\n\nWe're seeing a warning on ACME-DC01 that C: is at 92% capacity. Can you please investigate and free up some space?\n\nThanks,\nJane",
            'source'     => 'email',
            'is_internal' => false,
        ]);
        $t1->replies()->create([
            'user_id'    => $admin->id,
            'body'       => "Thanks Jane, I'll remote in now and check what's consuming the space. Will keep you posted.",
            'source'     => 'web',
            'is_internal' => false,
        ]);
        $t1->replies()->create([
            'user_id'    => $admin->id,
            'body'       => "Note: C:\\Windows\\SoftwareDistribution is 40GB. Looks like failed Windows Update cache. Running cleanup now.",
            'source'     => 'web',
            'is_internal' => true,
        ]);

        $t2 = Ticket::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'assigned_to' => $admin->id,
            'subject'    => 'User locked out of Office 365',
            'status'     => 'resolved',
            'priority'   => 'medium',
            'type'       => 'general',
            'source'     => 'phone',
            'resolved_at' => now()->subHours(2),
        ]);
        $t2->replies()->create([
            'user_id'    => $admin->id,
            'body'       => "Received call from Bob in accounts. Account locked due to too many failed login attempts from mobile. Reset MFA and unlocked account. Bob confirmed access restored.",
            'source'     => 'web',
            'is_internal' => false,
        ]);

        Ticket::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'subject'    => 'Backup job failed overnight — needs investigation',
            'status'     => 'open',
            'priority'   => 'urgent',
            'type'       => 'technical',
            'source'     => 'email',
        ])->replies()->create([
            'contact_id' => $contact?->id,
            'body'       => "We received an alert that last night's Veeam backup failed at 02:15. Error code: VeeamBackup_E_CANT_CONNECT. Please investigate ASAP as we need backup coverage restored.",
            'source'     => 'email',
            'is_internal' => false,
        ]);

        // Phase 4 — CRM demo data
        if (Pipeline::count() > 0) return;

        $pipeline = Pipeline::create([
            'company_id' => $company->id,
            'name'       => 'Sales Pipeline',
            'is_default' => true,
        ]);

        $stages = collect([
            ['name' => 'Prospecting',  'color' => 'gray',   'probability' => 10],
            ['name' => 'Qualified',    'color' => 'blue',   'probability' => 30],
            ['name' => 'Proposal',     'color' => 'purple', 'probability' => 50],
            ['name' => 'Negotiation',  'color' => 'yellow', 'probability' => 75],
            ['name' => 'Closed Won',   'color' => 'green',  'probability' => 100],
        ])->map(fn ($s, $i) => $pipeline->stages()->create(array_merge($s, ['sort_order' => $i])));

        $stageMap = $stages->keyBy('name');

        $d1 = Deal::create([
            'company_id'          => $company->id,
            'client_id'           => $client->id,
            'contact_id'          => $contact?->id,
            'pipeline_id'         => $pipeline->id,
            'stage_id'            => $stageMap['Proposal']->id,
            'assigned_to'         => $admin->id,
            'name'                => 'Acme — Full Network Refresh',
            'value'               => 18500.00,
            'status'              => 'open',
            'expected_close_date' => now()->addDays(21)->toDateString(),
            'notes'               => 'Replace aging Cisco switches and upgrade Wi-Fi to Meraki MR46.',
        ]);

        Activity::create([
            'company_id'  => $company->id,
            'user_id'     => $admin->id,
            'deal_id'     => $d1->id,
            'client_id'   => $client->id,
            'type'        => 'call',
            'subject'     => 'Discovery call with Jane Doe',
            'description' => 'Discussed current pain points: slow Wi-Fi, VLAN sprawl. Jane confirmed budget approved.',
            'outcome'     => 'Proceed to formal proposal.',
            'completed_at' => now()->subDays(3),
        ]);

        Activity::create([
            'company_id'  => $company->id,
            'user_id'     => $admin->id,
            'deal_id'     => $d1->id,
            'client_id'   => $client->id,
            'type'        => 'email',
            'subject'     => 'Sent proposal v1.0',
            'description' => 'Sent detailed scope + pricing for network refresh project.',
            'completed_at' => now()->subDay(),
        ]);

        Activity::create([
            'company_id' => $company->id,
            'user_id'    => $admin->id,
            'deal_id'    => $d1->id,
            'client_id'  => $client->id,
            'type'       => 'task',
            'subject'    => 'Follow up on proposal — awaiting sign-off',
            'due_at'     => now()->addDays(3),
        ]);

        $globex = Client::where('name', 'Globex Industries')->first();
        Deal::create([
            'company_id'          => $company->id,
            'client_id'           => $globex?->id,
            'pipeline_id'         => $pipeline->id,
            'stage_id'            => $stageMap['Qualified']->id,
            'assigned_to'         => $admin->id,
            'name'                => 'Globex — Managed Services Onboarding',
            'value'               => 36000.00,
            'status'              => 'open',
            'expected_close_date' => now()->addDays(45)->toDateString(),
            'notes'               => 'Annual MSP contract. 40 seats. Includes M365, backup, monitoring.',
        ]);

        // Phase 5 — Billing demo data
        if (Invoice::count() > 0) return;

        // Invoice 1 — paid
        $inv1 = Invoice::create([
            'company_id'     => $company->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-0001',
            'status'         => 'paid',
            'currency'       => 'USD',
            'issue_date'     => now()->subDays(45)->toDateString(),
            'due_date'       => now()->subDays(15)->toDateString(),
            'notes'          => 'Thank you for your business.',
            'subtotal'       => 0,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
            'paid_at'        => now()->subDays(10),
        ]);
        $inv1->items()->createMany([
            ['description' => 'Managed IT Support — May', 'quantity' => 1, 'unit_price' => 1500.00, 'tax_rate' => 10, 'amount' => 1500.00, 'sort_order' => 0],
            ['description' => 'Remote Monitoring & Management (40 seats × $5)', 'quantity' => 40, 'unit_price' => 5.00, 'tax_rate' => 10, 'amount' => 200.00, 'sort_order' => 1],
            ['description' => 'Microsoft 365 Business Premium (25 seats)', 'quantity' => 25, 'unit_price' => 22.00, 'tax_rate' => 10, 'amount' => 550.00, 'sort_order' => 2],
        ]);
        $inv1->recalculate();
        Payment::create([
            'company_id' => $company->id,
            'client_id'  => $client->id,
            'invoice_id' => $inv1->id,
            'amount'     => $inv1->fresh()->total,
            'currency'   => 'USD',
            'method'     => 'bank_transfer',
            'reference'  => 'TRN-1001',
            'paid_at'    => now()->subDays(10)->toDateString(),
        ]);
        $inv1->recalculate();

        // Invoice 2 — sent / outstanding
        $inv2 = Invoice::create([
            'company_id'     => $company->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-0002',
            'status'         => 'sent',
            'currency'       => 'USD',
            'issue_date'     => now()->subDays(5)->toDateString(),
            'due_date'       => now()->addDays(25)->toDateString(),
            'notes'          => 'June managed services.',
            'subtotal'       => 0,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
            'sent_at'        => now()->subDays(5),
        ]);
        $inv2->items()->createMany([
            ['description' => 'Managed IT Support — June', 'quantity' => 1, 'unit_price' => 1500.00, 'tax_rate' => 10, 'amount' => 1500.00, 'sort_order' => 0],
            ['description' => 'Remote Monitoring & Management (40 seats × $5)', 'quantity' => 40, 'unit_price' => 5.00, 'tax_rate' => 10, 'amount' => 200.00, 'sort_order' => 1],
            ['description' => 'Emergency On-Site Call — DC01 disk cleanup (2 hrs)', 'quantity' => 2, 'unit_price' => 150.00, 'tax_rate' => 0, 'amount' => 300.00, 'sort_order' => 2],
        ]);
        $inv2->recalculate();

        // Expenses
        Expense::create([
            'company_id'   => $company->id,
            'user_id'      => $admin->id,
            'client_id'    => $client->id,
            'category'     => 'software',
            'description'  => 'Veeam Backup license renewal',
            'amount'       => 349.00,
            'vendor'       => 'Veeam',
            'expense_date' => now()->subDays(30)->toDateString(),
            'is_billable'  => true,
            'currency'     => 'USD',
        ]);
        Expense::create([
            'company_id'   => $company->id,
            'user_id'      => $admin->id,
            'category'     => 'software',
            'description'  => 'ITFlow-Next server hosting — monthly',
            'amount'       => 45.00,
            'vendor'       => 'DigitalOcean',
            'expense_date' => now()->startOfMonth()->toDateString(),
            'is_billable'  => false,
            'currency'     => 'USD',
        ]);
        Expense::create([
            'company_id'   => $company->id,
            'user_id'      => $admin->id,
            'client_id'    => $client->id,
            'category'     => 'hardware',
            'description'  => 'Network switch replacement — Cisco SG350',
            'amount'       => 420.00,
            'vendor'       => 'Ingram Micro',
            'expense_date' => now()->subDays(10)->toDateString(),
            'is_billable'  => true,
            'currency'     => 'USD',
        ]);
    }
}
