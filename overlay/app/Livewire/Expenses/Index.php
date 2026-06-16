<?php

namespace App\Livewire\Expenses;

use App\Models\Client;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $category = '';

    public bool  $showModal  = false;
    public ?int  $editingId  = null;
    public array $form = [
        'description'  => '',
        'amount'       => '',
        'category'     => 'general',
        'vendor'       => '',
        'client_id'    => '',
        'expense_date' => '',
        'is_billable'  => false,
        'currency'     => 'USD',
    ];

    protected function rules(): array
    {
        return [
            'form.description'  => 'required|string|max:255',
            'form.amount'       => 'required|numeric|min:0.01',
            'form.category'     => 'required|in:general,software,hardware,travel,labour,other',
            'form.vendor'       => 'nullable|string|max:100',
            'form.client_id'    => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query
                    ->where('company_id', Auth::user()->company_id)
                    ->whereNull('archived_at')),
            ],
            'form.expense_date' => 'required|date',
            'form.is_billable'  => 'boolean',
            'form.currency'     => 'required|string|size:3',
        ];
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = ['description' => '', 'amount' => '', 'category' => 'general', 'vendor' => '', 'client_id' => '', 'expense_date' => today()->format('Y-m-d'), 'is_billable' => false, 'currency' => 'USD'];
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = Expense::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'description'  => $e->description,
            'amount'       => $e->amount,
            'category'     => $e->category,
            'vendor'       => $e->vendor ?? '',
            'client_id'    => $e->client_id ?? '',
            'expense_date' => $e->expense_date->format('Y-m-d'),
            'is_billable'  => $e->is_billable,
            'currency'     => $e->currency,
        ];
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $payload = array_merge($data['form'], [
            'company_id' => Auth::user()->company_id,
            'user_id'    => Auth::id(),
            'client_id'  => $data['form']['client_id'] ?: null,
        ]);

        if ($this->editingId) {
            Expense::where('company_id', Auth::user()->company_id)->findOrFail($this->editingId)->update($payload);
        } else {
            Expense::create($payload);
        }
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Expense::where('company_id', Auth::user()->company_id)->findOrFail($id)->delete();
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;
        $expenses = Expense::where('company_id', $companyId)
            ->with(['client', 'user'])
            ->when($this->search,   fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->orderBy('expense_date', 'desc')
            ->paginate(25);

        return view('livewire.expenses.index', [
            'expenses'   => $expenses,
            'clients'    => Client::active()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'totalMonth' => Expense::where('company_id', $companyId)
                ->whereMonth('expense_date', today()->month)
                ->whereYear('expense_date', today()->year)
                ->sum('amount'),
        ])->layout('components.layouts.app', ['header' => 'Expenses']);
    }
}
