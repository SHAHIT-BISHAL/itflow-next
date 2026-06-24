<?php

namespace App\Livewire\Tickets;

use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TicketEventRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $status   = '';
    public string $priority = '';
    public string $assignee = '';

    public bool  $showModal  = false;
    public array $form       = [
        'client_id'   => '',
        'subject'     => '',
        'priority'    => 'medium',
        'type'        => 'general',
        'assigned_to' => '',
        'body'        => '',
    ];

    protected function rules(): array
    {
        $companyId = Auth::user()->company_id;
        $user = Auth::user();

        return [
            'form.subject'     => 'required|string|max:255',
            'form.client_id'   => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->whereNull('archived_at')
                    ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('id', $user->permittedClients()->select('clients.id')))),
            ],
            'form.priority'    => 'required|in:low,medium,high,urgent',
            'form.type'        => 'required|in:general,technical,billing,other',
            'form.assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->whereNull('archived_at')),
            ],
            'form.body'        => 'required|string',
        ];
    }

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingStatus(): void  { $this->resetPage(); }
    public function updatingPriority(): void { $this->resetPage(); }
    public function updatingAssignee(): void { $this->resetPage(); }

    public function openModal(): void
    {
        $this->form = ['client_id' => '', 'subject' => '', 'priority' => 'medium', 'type' => 'general', 'assigned_to' => '', 'body' => ''];
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $user = Auth::user();
        $companyId = $user->company_id;

        if ($user->hasClientRestrictions() && empty($data['form']['client_id'])) {
            $this->addError('form.client_id', 'Select an accessible client.');
            return;
        }

        $ticket = Ticket::create([
            'company_id'  => $companyId,
            'client_id'   => $data['form']['client_id'] ?: null,
            'subject'     => $data['form']['subject'],
            'priority'    => $data['form']['priority'],
            'type'        => $data['form']['type'],
            'assigned_to' => $data['form']['assigned_to'] ?: null,
            'status'      => 'open',
            'source'      => 'web',
        ]);

        $reply = $ticket->replies()->create([
            'user_id'     => Auth::id(),
            'body'        => $data['form']['body'],
            'source'      => 'web',
            'is_internal' => false,
        ]);

        AuditLogger::record('ticket.created', $ticket, 'Ticket created.', null, AuditLogger::snapshot($ticket));
        TicketEventRecorder::record($ticket, 'ticket.initial_message_added', 'Initial ticket message added.', null, null, [
            'reply_id' => $reply->id,
        ]);

        $this->showModal = false;
        $this->redirect(route('tickets.show', $ticket), navigate: true);
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;
        $user = Auth::user();

        $tickets = Ticket::active()
            ->where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->with(['client', 'assignee', 'latestReply'])
            ->when($this->search,   fn ($q) => $q->where(function ($nested) {
                $nested
                    ->where('subject', 'like', "%{$this->search}%")
                    ->orWhere('ticket_number', 'like', "%{$this->search}%");
            }))
            ->when($this->status,   fn ($q) => $q->where('status', $this->status))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->when($this->assignee, fn ($q) => $q->where('assigned_to', $this->assignee))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('livewire.tickets.index', [
            'tickets' => $tickets,
            'clients' => Client::active()->where('company_id', $companyId)->visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'users'   => User::active()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
        ])->layout('components.layouts.app', ['header' => 'Tickets']);
    }
}
