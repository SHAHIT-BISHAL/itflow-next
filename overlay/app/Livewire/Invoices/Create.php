<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public ?int $invoiceId = null; // null = create, set = edit

    public array $form = [
        'client_id'      => '',
        'contact_id'     => '',
        'invoice_number' => '',
        'issue_date'     => '',
        'due_date'       => '',
        'currency'       => 'USD',
        'notes'          => '',
        'terms'          => '',
    ];

    public array $items = [];

    public array $contacts = [];

    protected function rules(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'form.client_id'      => [
                'required',
                Rule::exists('clients', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->whereNull('archived_at')),
            ],
            'form.contact_id'     => [
                'nullable',
                Rule::exists('contacts', 'id')->where(fn ($query) => $query
                    ->where('client_id', $this->form['client_id'] ?: 0)
                    ->whereNull('archived_at')),
            ],
            'form.invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($this->invoiceId),
            ],
            'form.issue_date'     => 'required|date',
            'form.due_date'       => 'required|date|after_or_equal:form.issue_date',
            'form.currency'       => 'required|string|size:3',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function mount(?Invoice $invoice = null): void
    {
        $companyId = Auth::user()->company_id;

        if ($invoice && $invoice->exists) {
            $this->invoiceId = $invoice->id;
            $this->form = $invoice->only(['client_id', 'contact_id', 'invoice_number', 'currency', 'notes', 'terms'])
                + ['issue_date' => $invoice->issue_date->format('Y-m-d'), 'due_date' => $invoice->due_date->format('Y-m-d')];
            $this->items = $invoice->items->map(fn ($i) => [
                'description' => $i->description,
                'quantity'    => (string) $i->quantity,
                'unit_price'  => (string) $i->unit_price,
                'tax_rate'    => (string) $i->tax_rate,
            ])->toArray();
            $this->loadContacts();
        } else {
            $this->form['invoice_number'] = Invoice::nextNumber($companyId);
            $this->form['issue_date']     = today()->format('Y-m-d');
            $this->form['due_date']       = today()->addDays(30)->format('Y-m-d');
            $this->addItem();
        }
    }

    public function updatedFormClientId(): void
    {
        $this->form['contact_id'] = '';
        $this->loadContacts();
    }

    public function loadContacts(): void
    {
        $this->contacts = $this->form['client_id']
            ? Contact::active()
                ->where('client_id', $this->form['client_id'])
                ->whereHas('client', fn ($query) => $query->where('company_id', Auth::user()->company_id))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray()
            : [];
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => '1', 'unit_price' => '', 'tax_rate' => '0'];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => (float)($i['quantity'] ?? 0) * (float)($i['unit_price'] ?? 0));
    }

    public function getTaxTotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => (float)($i['quantity'] ?? 0) * (float)($i['unit_price'] ?? 0) * (float)($i['tax_rate'] ?? 0) / 100);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->taxTotal;
    }

    public function save(string $action = 'draft'): void
    {
        $this->validate();

        $companyId = Auth::user()->company_id;

        $invoiceData = array_merge($this->form, [
            'company_id' => $companyId,
            'status'     => $action === 'send' ? 'sent' : 'draft',
            'sent_at'    => $action === 'send' ? now() : null,
            'client_id'  => $this->form['client_id'],
            'contact_id' => $this->form['contact_id'] ?: null,
        ]);

        if ($this->invoiceId) {
            $invoice = Invoice::where('company_id', $companyId)->findOrFail($this->invoiceId);
            $invoice->update($invoiceData);
            $invoice->items()->delete();
        } else {
            $invoice = Invoice::create($invoiceData);
        }

        foreach ($this->items as $i => $item) {
            $amount = (float)$item['quantity'] * (float)$item['unit_price'];
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'tax_rate'    => $item['tax_rate'] ?: 0,
                'amount'      => $amount,
                'sort_order'  => $i,
            ]);
        }

        $invoice->recalculate();

        $this->redirect(route('invoices.show', $invoice), navigate: true);
    }

    public function render()
    {
        return view('livewire.invoices.create', [
            'clients'  => Client::active()->where('company_id', Auth::user()->company_id)->orderBy('name')->get(['id', 'name']),
            'contacts' => $this->contacts,
        ])->layout('components.layouts.app', ['header' => $this->invoiceId ? 'Edit Invoice' : 'New Invoice']);
    }
}
