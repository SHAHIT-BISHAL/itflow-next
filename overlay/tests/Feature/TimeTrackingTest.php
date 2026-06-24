<?php

namespace Tests\Feature;

use App\Livewire\Tickets\Show as TicketsShow;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimeTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->user->assignRole('Administrator');

        $this->actingAs($this->user);
    }

    private function ticket(): Ticket
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);

        return Ticket::factory()->create([
            'company_id' => $this->company->id,
            'client_id'  => $client->id,
        ]);
    }

    public function test_logging_time_persists_scoped_and_records_audit_and_event(): void
    {
        $ticket = $this->ticket();

        Livewire::test(TicketsShow::class, ['ticket' => $ticket])
            ->set('timeForm.minutes', 90)
            ->set('timeForm.description', 'Investigated disk space issue')
            ->set('timeForm.is_billable', true)
            ->set('timeForm.performed_at', today()->format('Y-m-d'))
            ->call('logTime')
            ->assertHasNoErrors();

        $entry = TimeEntry::firstOrFail();

        $this->assertSame($this->company->id, $entry->company_id);
        $this->assertSame($ticket->id, $entry->ticket_id);
        $this->assertSame($ticket->client_id, $entry->client_id);
        $this->assertSame($this->user->id, $entry->user_id);
        $this->assertSame(90, $entry->minutes);
        $this->assertSame('1h 30m', $entry->formatted_duration);
        $this->assertSame(90, $ticket->fresh()->total_minutes);

        $this->assertDatabaseHas('audit_logs', [
            'company_id'   => $this->company->id,
            'subject_type' => Ticket::class,
            'subject_id'   => $ticket->id,
            'action'       => 'ticket.time_logged',
        ]);
        $this->assertDatabaseHas('ticket_events', [
            'ticket_id'  => $ticket->id,
            'event_type' => 'ticket.time_logged',
        ]);
    }

    public function test_minutes_and_description_are_required(): void
    {
        $ticket = $this->ticket();

        Livewire::test(TicketsShow::class, ['ticket' => $ticket])
            ->set('timeForm.minutes', '')
            ->set('timeForm.description', '')
            ->call('logTime')
            ->assertHasErrors(['timeForm.minutes', 'timeForm.description']);
    }

    public function test_uninvoiced_entry_can_be_deleted_but_invoiced_cannot(): void
    {
        $ticket = $this->ticket();

        $open = TimeEntry::factory()->create([
            'company_id' => $this->company->id,
            'ticket_id'  => $ticket->id,
            'user_id'    => $this->user->id,
            'client_id'  => $ticket->client_id,
        ]);
        $invoice = Invoice::create([
            'company_id'     => $this->company->id,
            'client_id'      => $ticket->client_id,
            'invoice_number' => 'INV-9001',
            'status'         => 'draft',
            'issue_date'     => today(),
            'due_date'       => today()->addDays(30),
            'currency'       => 'USD',
        ]);
        $invoiced = TimeEntry::factory()->create([
            'company_id'  => $this->company->id,
            'ticket_id'   => $ticket->id,
            'user_id'     => $this->user->id,
            'client_id'   => $ticket->client_id,
            'invoice_id'  => $invoice->id,
            'invoiced_at' => now(),
        ]);

        $component = Livewire::test(TicketsShow::class, ['ticket' => $ticket]);

        $component->call('deleteTimeEntry', $open->id);
        $this->assertDatabaseMissing('time_entries', ['id' => $open->id]);

        $component->call('deleteTimeEntry', $invoiced->id);
        $this->assertDatabaseHas('time_entries', ['id' => $invoiced->id]);
    }

    public function test_time_logging_is_company_scoped(): void
    {
        $otherCompany = Company::factory()->create();
        $foreignClient = Client::factory()->create(['company_id' => $otherCompany->id]);
        $foreignTicket = Ticket::factory()->create([
            'company_id' => $otherCompany->id,
            'client_id'  => $foreignClient->id,
        ]);

        $this->get(route('tickets.show', $foreignTicket))->assertNotFound();
    }
}
