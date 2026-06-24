<?php

namespace Tests\Feature;

use App\Livewire\Documents\Manager as DocumentsManager;
use App\Livewire\Passwords\Manager as PasswordsManager;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Company;
use App\Models\Password;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentationDepthTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $company = Company::create(['name' => 'Test Co']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('Administrator');
        $this->actingAs($user);

        $this->client = Client::create([
            'company_id' => $company->id,
            'name' => 'Acme Test',
            'net_terms' => 30,
        ]);
    }

    public function test_document_save_creates_version_and_related_records(): void
    {
        $asset = Asset::create([
            'company_id' => $this->client->company_id,
            'client_id' => $this->client->id,
            'name' => 'ACME-FW01',
            'asset_type' => 'Network',
        ]);

        Livewire::test(DocumentsManager::class, ['client' => $this->client])
            ->call('create')
            ->set('title', 'Firewall Runbook')
            ->set('document_type', 'runbook')
            ->set('content', 'Restart procedure and escalation notes.')
            ->set('review_due_at', now()->addMonth()->toDateString())
            ->set('assetIds', [$asset->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'client_id' => $this->client->id,
            'title' => 'Firewall Runbook',
            'document_type' => 'runbook',
        ]);

        $this->assertDatabaseHas('document_versions', [
            'title' => 'Firewall Runbook',
            'version_number' => 1,
            'change_summary' => 'Initial version',
        ]);

        $this->assertDatabaseHas('document_relations', [
            'related_type' => Asset::class,
            'related_id' => $asset->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => \App\Models\Document::class,
            'action' => 'document.created',
        ]);
    }

    public function test_password_reveal_is_audited(): void
    {
        $password = Password::create([
            'company_id' => $this->client->company_id,
            'client_id' => $this->client->id,
            'name' => 'Domain Admin',
            'username' => 'administrator',
            'password' => 'secret',
        ]);

        Livewire::test(PasswordsManager::class, ['client' => $this->client])
            ->call('toggleReveal', $password->id);

        $this->assertDatabaseHas('password_access_logs', [
            'company_id' => $this->client->company_id,
            'client_id' => $this->client->id,
            'password_id' => $password->id,
            'action' => 'reveal',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $this->client->company_id,
            'subject_type' => Password::class,
            'subject_id' => $password->id,
            'action' => 'password.reveal',
        ]);
    }
}
