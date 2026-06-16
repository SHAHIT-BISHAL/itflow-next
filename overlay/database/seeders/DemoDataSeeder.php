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
        Asset::create([
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

        Domain::create([
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
    }
}
