<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public bool  $showPaymentModal = false;
    public array $paymentForm = [
        'amount'    => '',
        'method'    => 'bank_transfer',
        'reference' => '',
        'paid_at'   => '',
        'notes'     => '',
    ];

    protected array $paymentRules = [
        'paymentForm.amount'    => 'required|numeric|min:0.01',
        'paymentForm.method'    => 'required|in:cash,check,card,bank_transfer,other',
        'paymentForm.reference' => 'nullable|string|max:100',
        'paymentForm.paid_at'   => 'required|date',
        'paymentForm.notes'     => 'nullable|string',
    ];

    public function mount(Invoice $invoice): void
    {
        $user = Auth::user();

        abort_if($invoice->company_id !== $user->company_id, 404);
        abort_if(! $user->canAccessClient($invoice->client), 404);

        $this->invoice = $invoice;
        $this->paymentForm['paid_at']  = today()->format('Y-m-d');
        $this->paymentForm['amount']   = number_format($invoice->amount_due, 2, '.', '');
    }

    public function openPaymentModal(): void
    {
        $this->paymentForm['amount'] = number_format($this->invoice->amount_due, 2, '.', '');
        $this->showPaymentModal = true;
    }

    public function recordPayment(): void
    {
        $data = $this->validate($this->paymentRules);
        $amount = (float) $data['paymentForm']['amount'];
        $invoiceBefore = AuditLogger::snapshot($this->invoice);

        if ($this->invoice->status === 'void' || $this->invoice->amount_due <= 0) {
            $this->addError('paymentForm.amount', 'This invoice cannot accept another payment.');
            return;
        }

        if ($amount > $this->invoice->amount_due) {
            $this->addError('paymentForm.amount', 'Payment cannot exceed the invoice amount due.');
            return;
        }

        $payment = Payment::create([
            'company_id' => Auth::user()->company_id,
            'client_id'  => $this->invoice->client_id,
            'invoice_id' => $this->invoice->id,
            'amount'     => $amount,
            'currency'   => $this->invoice->currency,
            'method'     => $data['paymentForm']['method'],
            'reference'  => $data['paymentForm']['reference'] ?: null,
            'paid_at'    => $data['paymentForm']['paid_at'],
            'notes'      => $data['paymentForm']['notes'] ?: null,
        ]);

        $this->invoice->recalculate();
        $this->invoice->refresh();
        AuditLogger::record('payment.recorded', $payment, 'Payment recorded.', null, AuditLogger::snapshot($payment));
        AuditLogger::record('invoice.payment_applied', $this->invoice, 'Invoice payment applied.', $invoiceBefore, AuditLogger::snapshot($this->invoice), [
            'payment_id' => $payment->id,
        ]);
        $this->showPaymentModal = false;
        $this->dispatch('toast', message: 'Payment of $' . number_format($amount, 2) . ' recorded.', type: 'success');
    }

    public function markVoid(): void
    {
        $before = AuditLogger::snapshot($this->invoice);
        $this->invoice->update(['status' => 'void']);
        $this->invoice->refresh();
        AuditLogger::record('invoice.voided', $this->invoice, 'Invoice marked void.', $before, AuditLogger::snapshot($this->invoice));
        $this->dispatch('toast', message: 'Invoice marked as void.', type: 'info');
    }

    public function render()
    {
        return view('livewire.invoices.show', [
            'payments' => $this->invoice->payments()->orderBy('paid_at', 'desc')->get(),
        ])->layout('components.layouts.app', ['header' => $this->invoice->invoice_number]);
    }
}
