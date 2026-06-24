<x-mail::message>
# Invoice {{ $invoice->invoice_number }}

Please find your invoice details below.

<x-mail::table>
| | |
|:--|--:|
| **Invoice #** | {{ $invoice->invoice_number }} |
| **Issue Date** | {{ $invoice->issue_date->format('d M Y') }} |
| **Due Date** | {{ $invoice->due_date->format('d M Y') }} |
| **Amount Due** | ${{ number_format($invoice->amount_due, 2) }} {{ $invoice->currency }} |
</x-mail::table>

<x-mail::button :url="route('invoices.show', $invoice)">
View Invoice
</x-mail::button>

If you have any questions about this invoice, please don't hesitate to contact us.

Thanks,
{{ config('app.name') }}
</x-mail::message>
