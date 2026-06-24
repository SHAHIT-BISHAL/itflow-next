<?php

namespace Tests\Feature;

use App\Livewire\Clients\Index as ClientsIndex;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticatedAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::create(['name' => 'Test Co']);

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('Administrator');

        $this->actingAs($user);

        return $user;
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_view_clients_list(): void
    {
        $this->authenticatedAdmin();

        $this->get(route('clients.index'))->assertOk();
    }

    public function test_admin_can_create_a_client(): void
    {
        $this->authenticatedAdmin();

        Livewire::test(ClientsIndex::class)
            ->call('create')
            ->set('name', 'New Client Inc')
            ->set('net_terms', 30)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', ['name' => 'New Client Inc']);
    }

    public function test_admin_can_archive_a_client(): void
    {
        $this->authenticatedAdmin();

        $client = Client::factory()->create();

        Livewire::test(ClientsIndex::class)
            ->call('archive', $client->id);

        $this->assertNotNull($client->refresh()->archived_at);
    }

    public function test_restricted_user_can_only_view_permitted_clients(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::create(['name' => 'Restricted Co']);
        $allowed = Client::factory()->create(['company_id' => $company->id, 'name' => 'Allowed Client']);
        $blocked = Client::factory()->create(['company_id' => $company->id, 'name' => 'Blocked Client']);

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('Technician');
        $user->permittedClients()->attach($allowed->id);

        $this->actingAs($user);

        $this->get(route('clients.show', $allowed))->assertOk();
        $this->get(route('clients.show', $blocked))->assertNotFound();

        Livewire::test(ClientsIndex::class)
            ->assertSee('Allowed Client')
            ->assertDontSee('Blocked Client');
    }
}
