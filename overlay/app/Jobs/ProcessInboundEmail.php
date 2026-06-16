<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Contact;
use App\Models\MailAccount;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInboundEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int    $mailAccountId,
        public readonly string $fromEmail,
        public readonly string $fromName,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $messageId,
        public readonly ?string $inReplyTo = null,
    ) {}

    public function handle(): void
    {
        // Find the company from the mail account
        $account = MailAccount::find($this->mailAccountId);
        if (! $account) return;

        $companyId = $account->company_id;

        // Match sender to a known contact
        $contact = Contact::whereHas('client', fn ($q) => $q->where('company_id', $companyId))
            ->where('email', $this->fromEmail)
            ->first();

        $clientId  = $contact?->client_id;
        $contactId = $contact?->id;

        // Try to thread onto an existing ticket via In-Reply-To or subject match
        $ticket = null;

        if ($this->inReplyTo) {
            $ticket = Ticket::where('company_id', $companyId)
                ->where('email_message_id', $this->inReplyTo)
                ->first();

            if (! $ticket) {
                $existing = TicketReply::where('email_message_id', $this->inReplyTo)->first();
                $ticket   = $existing?->ticket;
            }
        }

        // Subject match fallback — strip Re:/Fwd: and find open ticket
        if (! $ticket) {
            $cleanSubject = preg_replace('/^(re|fwd?):\s*/i', '', trim($this->subject));
            $ticket = Ticket::where('company_id', $companyId)
                ->whereIn('status', ['open', 'pending'])
                ->where('subject', 'like', "%{$cleanSubject}%")
                ->latest()
                ->first();
        }

        if ($ticket) {
            // Add reply to existing ticket
            $ticket->replies()->create([
                'contact_id'       => $contactId,
                'body'             => $this->body,
                'is_internal'      => false,
                'source'           => 'email',
                'email_message_id' => $this->messageId,
            ]);

            // Re-open if closed/pending
            if (in_array($ticket->status, ['pending', 'resolved'])) {
                $ticket->update(['status' => 'open']);
            }
        } else {
            // Create new ticket
            $ticket = Ticket::create([
                'company_id'       => $companyId,
                'client_id'        => $clientId,
                'contact_id'       => $contactId,
                'subject'          => $this->subject,
                'status'           => 'open',
                'priority'         => 'medium',
                'type'             => 'general',
                'source'           => 'email',
                'email_message_id' => $this->messageId,
            ]);

            $ticket->replies()->create([
                'contact_id'       => $contactId,
                'body'             => $this->body,
                'is_internal'      => false,
                'source'           => 'email',
                'email_message_id' => $this->messageId,
            ]);
        }
    }
}
