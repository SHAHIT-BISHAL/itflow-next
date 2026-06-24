<x-mail::message>
# New Reply: {{ $ticket->subject }}

A new reply has been added to ticket #{{ $ticket->id }}.

<x-mail::panel>
{{ $reply->body }}
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $ticket)">
View Full Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
