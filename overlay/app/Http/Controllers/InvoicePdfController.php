<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        abort_unless($invoice->company_id === Auth::user()->company_id, 403);

        $invoice->load(['client', 'contact', 'items', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}
