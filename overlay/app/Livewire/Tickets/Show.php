<?php

namespace App\Livewire\Tickets;

use App\Mail\TicketReplied;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TicketEventRecorder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    public Ticket $ticket;

    public string $replyBody     = '';
    public bool   $isInternal    = false;
    public string $editStatus    = '';
    public string $editPriority  = '';
    public string $editAssignee  = '';

    protected array $rules = [
        'replyBody' => 'required|string',
    ];

    public function mount(Ticket $ticket): void
    {
        $user = Auth::user();

        abort_if($ticket->company_id !== $user->company_id, 404);
        abort_if($ticket->client ? ! $user->canAccessClient($ticket->client) : $user->hasClientRestrictions(), 404);

        $this->ticket      = $ticket;
        $this->editStatus   = $ticket->status;
        $this->editPriority = $ticket->priority;
        $this->editAssignee = (string) ($ticket->assigned_to ?? '');
    }

    public function sendReply(): void
    {
        $this->validate(['replyBody' => 'required|string']);

        $reply = $this->ticket->replies()->create([
            'user_id'     => Auth::id(),
            'body'        => $this->replyBody,
            'is_internal' => $this->isInternal,
            'source'      => 'web',
        ]);
        AuditLogger::record(
            $this->isInternal ? 'ticket.internal_note_added' : 'ticket.reply_sent',
            $this->ticket,
            $this->isInternal ? 'Internal ticket note added.' : 'Ticket reply sent.',
            null,
            null,
            ['reply_id' => $reply->id],
        );
        TicketEventRecorder::record(
            $this->ticket,
            $this->isInternal ? 'ticket.internal_note_added' : 'ticket.reply_sent',
            $this->isInternal ? 'Internal note added.' : 'Reply sent.',
            null,
            null,
            ['reply_id' => $reply->id],
        );

        if (! $this->isInternal && $this->ticket->status === 'pending') {
            $before = AuditLogger::snapshot($this->ticket);
            $this->ticket->update(['status' => 'open']);
            TicketEventRecorder::record($this->ticket, 'ticket.reopened', 'Ticket reopened after reply.', $before, AuditLogger::snapshot($this->ticket));
        }

        // Notify the contact if there is one and the reply is not internal
        if (! $this->isInternal && $this->ticket->contact?->email) {
            Mail::to($this->ticket->contact->email)
                ->queue(new TicketReplied($this->ticket, $reply));
        }

        $this->replyBody  = '';
        $this->isInternal = false;
        $this->ticket->refresh();
        $this->dispatch('toast', message: $reply->is_internal ? 'Internal note added.' : 'Reply sent.', type: 'success');
    }

    public function updateMeta(): void
    {
        $before = AuditLogger::snapshot($this->ticket);

        $data = $this->validate([
            'editStatus'   => 'required|in:open,pending,resolved,closed',
            'editPriority' => 'required|in:low,medium,high,urgent',
            'editAssignee' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('company_id', Auth::user()->company_id)
                    ->whereNull('archived_at')),
            ],
        ]);

        $this->ticket->update([
            'status'      => $data['editStatus'],
            'priority'    => $data['editPriority'],
            'assigned_to' => $data['editAssignee'] ?: null,
            'resolved_at' => in_array($data['editStatus'], ['resolved', 'closed']) ? ($this->ticket->resolved_at ?? now()) : null,
            'closed_at'   => $data['editStatus'] === 'closed' ? ($this->ticket->closed_at ?? now()) : null,
        ]);

        $this->ticket->refresh();
        AuditLogger::record('ticket.updated', $this->ticket, 'Ticket metadata updated.', $before, AuditLogger::snapshot($this->ticket));
        TicketEventRecorder::record($this->ticket, 'ticket.updated', 'Ticket metadata updated.', $before, AuditLogger::snapshot($this->ticket));
        $this->dispatch('toast', message: 'Ticket updated.', type: 'success');
    }

    public function render()
    {
        return view('livewire.tickets.show', [
            'replies' => $this->ticket->replies()->with(['user', 'contact', 'attachments'])->get(),
            'events' => $this->ticket->events()->with('actor')->take(25)->get(),
            'users'   => User::active()->where('company_id', Auth::user()->company_id)->orderBy('name')->get(['id', 'name']),
        ])->layout('components.layouts.app', ['header' => "Ticket {$this->ticket->display_number}"]);
    }
}
