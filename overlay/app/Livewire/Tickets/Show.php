<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $this->ticket      = $ticket;
        $this->editStatus   = $ticket->status;
        $this->editPriority = $ticket->priority;
        $this->editAssignee = (string) ($ticket->assigned_to ?? '');
    }

    public function sendReply(): void
    {
        $this->validate(['replyBody' => 'required|string']);

        $this->ticket->replies()->create([
            'user_id'     => Auth::id(),
            'body'        => $this->replyBody,
            'is_internal' => $this->isInternal,
            'source'      => 'web',
        ]);

        if (! $this->isInternal && $this->ticket->status === 'pending') {
            $this->ticket->update(['status' => 'open']);
        }

        $this->replyBody  = '';
        $this->isInternal = false;
        $this->ticket->refresh();
    }

    public function updateMeta(): void
    {
        $this->ticket->update([
            'status'      => $this->editStatus,
            'priority'    => $this->editPriority,
            'assigned_to' => $this->editAssignee ?: null,
            'resolved_at' => in_array($this->editStatus, ['resolved', 'closed']) ? ($this->ticket->resolved_at ?? now()) : null,
            'closed_at'   => $this->editStatus === 'closed' ? ($this->ticket->closed_at ?? now()) : null,
        ]);

        $this->ticket->refresh();
        session()->flash('success', 'Ticket updated.');
    }

    public function render()
    {
        return view('livewire.tickets.show', [
            'replies' => $this->ticket->replies()->with(['user', 'contact', 'attachments'])->get(),
            'users'   => User::orderBy('name')->get(['id', 'name']),
        ])->layout('components.layouts.app', ['header' => "Ticket #{$this->ticket->id}"]);
    }
}
