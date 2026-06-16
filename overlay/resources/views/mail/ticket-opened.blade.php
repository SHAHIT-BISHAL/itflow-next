<x-mail::message>
# Ticket Opened: {{ $ticket->subject }}

A new support ticket has been created.

**Ticket #:** {{ $ticket->id }}
**Priority:** {{ ucfirst($ticket->priority) }}
**Status:** {{ ucfirst($ticket->status) }}
@if($ticket->client)
**Client:** {{ $ticket->client->name }}
@endif

<x-mail::button :url="route('tickets.show', $ticket)">
View Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
