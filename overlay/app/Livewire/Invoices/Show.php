<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\Payment;
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
        abort_if($invoice->company_id !== Auth::user()->company_id, 404);

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

        if ($this->invoice->status === 'void' || $this->invoice->amount_due <= 0) {
            $this->addError('paymentForm.amount', 'This invoice cannot accept another payment.');
            return;
        }

        if ($amount > $this->invoice->amount_due) {
            $this->addError('paymentForm.amount', 'Payment cannot exceed the invoice amount due.');
            return;
        }

        Payment::create([
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
        $this->showPaymentModal = false;
        $this->dispatch('toast', message: 'Payment of $' . number_format($amount, 2) . ' recorded.', type: 'success');
    }

    public function markVoid(): void
    {
        $this->invoice->update(['status' => 'void']);
        $this->invoice->refresh();
        $this->dispatch('toast', message: 'Invoice marked as void.', type: 'info');
    }

    public function render()
    {
        return view('livewire.invoices.show', [
            'payments' => $this->invoice->payments()->orderBy('paid_at', 'desc')->get(),
        ])->layout('components.layouts.app', ['header' => $this->invoice->invoice_number]);
    }
}
