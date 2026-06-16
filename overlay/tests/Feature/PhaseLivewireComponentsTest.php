<?php

namespace Tests\Feature;

use App\Livewire\Deals\Index as DealsIndex;
use App\Livewire\Deals\Show as DealsShow;
use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Livewire\GlobalSearch;
use App\Livewire\Invoices\Create as InvoicesCreate;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoicesShow;
use App\Livewire\Reports\Expenses as ExpensesReport;
use App\Livewire\Reports\Overview as OverviewReport;
use App\Livewire\Reports\Revenue as RevenueReport;
use App\Livewire\Reports\Tickets as TicketsReport;
use App\Livewire\Tickets\Index as TicketsIndex;
use App\Livewire\Tickets\Show as TicketsShow;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseLivewireComponentsTest extends TestCase
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

    public function test_invoice_index_renders_and_is_company_scoped(): void
    {
        $ownClient = Client::factory()->create(['company_id' => $this->company->id, 'name' => 'Visible Billing Client']);
        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Hidden Billing Client']);

        $ownInvoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $ownClient->id,
            'invoice_number' => 'INV-VISIBLE',
        ]);
        Invoice::factory()->create([
            'company_id' => $otherCompany->id,
            'client_id' => $otherClient->id,
            'invoice_number' => 'INV-HIDDEN',
        ]);

        $this->get(route('invoices.index'))->assertOk();

        Livewire::test(InvoicesIndex::class)
            ->assertSee($ownInvoice->invoice_number)
            ->assertDontSee('INV-HIDDEN')
            ->assertDontSee('Hidden Billing Client');
    }

    public function test_invoice_create_persists_items_and_recalculates_totals(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);

        Livewire::test(InvoicesCreate::class)
            ->set('form.client_id', $client->id)
            ->set('form.invoice_number', 'INV-CREATE-001')
            ->set('form.issue_date', today()->toDateString())
            ->set('form.due_date', today()->addDays(30)->toDateString())
            ->set('items', [
                ['description' => 'Managed services', 'quantity' => '2', 'unit_price' => '150', 'tax_rate' => '10'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('invoice_number', 'INV-CREATE-001')->firstOrFail();

        $this->assertSame($this->company->id, $invoice->company_id);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Managed services',
            'amount' => 300,
        ]);
        $this->assertSame('300.00', $invoice->subtotal);
        $this->assertSame('30.00', $invoice->tax_amount);
        $this->assertSame('330.00', $invoice->total);
    }

    public function test_invoice_create_edit_rejects_another_company_invoice(): void
    {
        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $otherCompany->id,
            'client_id' => $otherClient->id,
        ]);

        $this->get(route('invoices.edit', $invoice))->assertNotFound();
    }

    public function test_invoice_show_records_payment_and_recalculates(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'status' => 'sent',
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
            'unit_price' => 200,
            'tax_rate' => 0,
            'amount' => 200,
        ]);
        $invoice->recalculate();

        Livewire::test(InvoicesShow::class, ['invoice' => $invoice->fresh()])
            ->set('paymentForm.amount', '200.00')
            ->set('paymentForm.method', 'bank_transfer')
            ->set('paymentForm.reference', 'PAY-001')
            ->set('paymentForm.paid_at', today()->toDateString())
            ->call('recordPayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
            'amount' => 200,
            'reference' => 'PAY-001',
        ]);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('200.00', $invoice->fresh()->amount_paid);
    }

    public function test_invoice_show_rejects_another_company_invoice(): void
    {
        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $otherCompany->id,
            'client_id' => $otherClient->id,
        ]);

        $this->get(route('invoices.show', $invoice))->assertNotFound();
    }

    public function test_expenses_index_renders_is_scoped_and_saves(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $otherCompany = Company::factory()->create();

        Expense::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'description' => 'Visible Expense',
        ]);
        Expense::factory()->create([
            'company_id' => $otherCompany->id,
            'description' => 'Hidden Expense',
        ]);

        $this->get(route('expenses.index'))->assertOk();

        Livewire::test(ExpensesIndex::class)
            ->assertSee('Visible Expense')
            ->assertDontSee('Hidden Expense')
            ->call('openCreate')
            ->set('form.description', 'New Billable Expense')
            ->set('form.amount', '49.50')
            ->set('form.category', 'software')
            ->set('form.vendor', 'VendorCo')
            ->set('form.client_id', $client->id)
            ->set('form.expense_date', today()->toDateString())
            ->set('form.is_billable', true)
            ->set('form.currency', 'USD')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expenses', [
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'description' => 'New Billable Expense',
            'amount' => 49.50,
            'is_billable' => true,
        ]);
    }

    public function test_deals_index_renders_list_and_kanban_scoped_and_saves(): void
    {
        [$pipeline, $stage] = $this->pipelineWithStage($this->company);
        $client = Client::factory()->create(['company_id' => $this->company->id, 'name' => 'Visible Deal Client']);
        $otherCompany = Company::factory()->create();
        [$otherPipeline, $otherStage] = $this->pipelineWithStage($otherCompany);

        Deal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'name' => 'Visible Deal',
        ]);
        Deal::factory()->create([
            'company_id' => $otherCompany->id,
            'pipeline_id' => $otherPipeline->id,
            'stage_id' => $otherStage->id,
            'name' => 'Hidden Deal',
        ]);

        $this->get(route('deals.index'))->assertOk();

        Livewire::test(DealsIndex::class)
            ->assertSee('Visible Deal')
            ->assertDontSee('Hidden Deal')
            ->set('view', 'kanban')
            ->assertSee($stage->name)
            ->assertSee('Visible Deal')
            ->call('openModal')
            ->set('form.name', 'Created Deal')
            ->set('form.client_id', $client->id)
            ->set('form.pipeline_id', $pipeline->id)
            ->set('form.stage_id', $stage->id)
            ->set('form.value', '1234')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('deals', [
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'stage_id' => $stage->id,
            'name' => 'Created Deal',
            'value' => 1234,
        ]);
    }

    public function test_deal_show_rejects_foreign_deal_and_logs_activity(): void
    {
        [$pipeline, $stage] = $this->pipelineWithStage($this->company);
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
        ]);

        Livewire::test(DealsShow::class, ['deal' => $deal])
            ->call('openActivityModal')
            ->set('activityForm.type', 'call')
            ->set('activityForm.subject', 'Discovery call')
            ->set('activityForm.description', 'Discussed scope.')
            ->call('saveActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activities', [
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'deal_id' => $deal->id,
            'client_id' => $client->id,
            'subject' => 'Discovery call',
        ]);

        $otherCompany = Company::factory()->create();
        [$otherPipeline, $otherStage] = $this->pipelineWithStage($otherCompany);
        $foreignDeal = Deal::factory()->create([
            'company_id' => $otherCompany->id,
            'pipeline_id' => $otherPipeline->id,
            'stage_id' => $otherStage->id,
        ]);

        $this->get(route('deals.show', $foreignDeal))->assertNotFound();
    }

    public function test_tickets_index_renders_scoped_and_creates_ticket_with_reply(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $otherCompany = Company::factory()->create();

        Ticket::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'subject' => 'Visible Ticket',
        ]);
        Ticket::factory()->create([
            'company_id' => $otherCompany->id,
            'subject' => 'Hidden Ticket',
        ]);

        $this->get(route('tickets.index'))->assertOk();

        Livewire::test(TicketsIndex::class)
            ->assertSee('Visible Ticket')
            ->assertDontSee('Hidden Ticket')
            ->call('openModal')
            ->set('form.client_id', $client->id)
            ->set('form.subject', 'Created Ticket')
            ->set('form.priority', 'high')
            ->set('form.type', 'technical')
            ->set('form.body', 'Initial request')
            ->call('save')
            ->assertHasNoErrors();

        $ticket = Ticket::where('subject', 'Created Ticket')->firstOrFail();

        $this->assertSame($this->company->id, $ticket->company_id);
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'body' => 'Initial request',
        ]);
    }

    public function test_ticket_show_rejects_foreign_ticket_sends_reply_and_updates_meta(): void
    {
        Mail::fake();

        $ticket = Ticket::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        Livewire::test(TicketsShow::class, ['ticket' => $ticket])
            ->set('replyBody', 'Working on this now.')
            ->call('sendReply')
            ->assertHasNoErrors()
            ->set('editStatus', 'resolved')
            ->set('editPriority', 'high')
            ->call('updateMeta')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->user->id,
            'body' => 'Working on this now.',
            'is_internal' => false,
        ]);
        $this->assertSame('resolved', $ticket->fresh()->status);
        $this->assertSame('high', $ticket->fresh()->priority);
        $this->assertNotNull($ticket->fresh()->resolved_at);

        $foreign = Ticket::factory()->create(['company_id' => Company::factory()->create()->id]);

        $this->get(route('tickets.show', $foreign))->assertNotFound();
    }

    public function test_reports_render_company_scoped_aggregates(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id, 'name' => 'Scoped Revenue Client']);
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'status' => 'paid',
            'total' => 250,
            'amount_paid' => 250,
        ]);
        Payment::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
            'amount' => 250,
            'paid_at' => today()->toDateString(),
        ]);
        Expense::factory()->create([
            'company_id' => $this->company->id,
            'amount' => 80,
            'category' => 'software',
            'expense_date' => today()->toDateString(),
            'is_billable' => true,
        ]);
        Ticket::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'subject' => 'Scoped Ticket',
            'status' => 'open',
            'priority' => 'urgent',
        ]);

        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Foreign Revenue Client']);
        $otherInvoice = Invoice::factory()->create([
            'company_id' => $otherCompany->id,
            'client_id' => $otherClient->id,
        ]);
        Payment::factory()->create([
            'company_id' => $otherCompany->id,
            'client_id' => $otherClient->id,
            'invoice_id' => $otherInvoice->id,
            'amount' => 999,
            'paid_at' => today()->toDateString(),
        ]);
        Expense::factory()->create([
            'company_id' => $otherCompany->id,
            'amount' => 999,
            'expense_date' => today()->toDateString(),
        ]);
        Ticket::factory()->create([
            'company_id' => $otherCompany->id,
            'subject' => 'Foreign Ticket',
        ]);

        $this->get(route('reports.index'))->assertOk();
        $this->get(route('reports.revenue'))->assertOk();
        $this->get(route('reports.tickets'))->assertOk();
        $this->get(route('reports.expenses'))->assertOk();

        Livewire::test(OverviewReport::class)
            ->assertSee('$250')
            ->assertSee('$80');

        Livewire::test(RevenueReport::class)
            ->assertSee('$250.00')
            ->assertSee('Scoped Revenue Client')
            ->assertDontSee('Foreign Revenue Client')
            ->assertDontSee('$999.00');

        Livewire::test(TicketsReport::class)
            ->assertSee('Scoped Revenue Client')
            ->assertDontSee('Foreign Ticket');

        Livewire::test(ExpensesReport::class)
            ->assertSee('$80.00')
            ->assertSee('Software')
            ->assertDontSee('$999.00');
    }

    public function test_global_search_respects_company_scope_and_minimum_query_length(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id, 'name' => 'Acme Scoped']);
        Ticket::factory()->create(['company_id' => $this->company->id, 'client_id' => $client->id, 'subject' => 'Acme Printer']);
        Invoice::factory()->create(['company_id' => $this->company->id, 'client_id' => $client->id, 'invoice_number' => 'ACME-001']);
        [$pipeline, $stage] = $this->pipelineWithStage($this->company);
        Deal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'name' => 'Acme Upgrade',
        ]);

        $otherCompany = Company::factory()->create();
        $otherClient = Client::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Acme Hidden']);
        Ticket::factory()->create(['company_id' => $otherCompany->id, 'client_id' => $otherClient->id, 'subject' => 'Acme Hidden Ticket']);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'A')
            ->assertSet('open', false)
            ->assertDontSee('Acme Scoped')
            ->set('query', 'Ac')
            ->assertSet('open', true)
            ->assertSee('Acme Scoped')
            ->assertSee('Acme Printer')
            ->assertSee('ACME-001')
            ->assertSee('Acme Upgrade')
            ->assertDontSee('Acme Hidden');
    }

    private function pipelineWithStage(Company $company): array
    {
        $pipeline = Pipeline::factory()->create([
            'company_id' => $company->id,
            'name' => 'Sales Pipeline '.$company->id,
            'is_default' => true,
        ]);
        $stage = PipelineStage::factory()->create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Qualified '.$company->id,
        ]);

        return [$pipeline, $stage];
    }
}
