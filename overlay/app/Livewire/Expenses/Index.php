<?php

namespace App\Livewire\Expenses;

use App\Models\Client;
use App\Models\Expense;
use App\Services\AuditLogger;
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
        $user = Auth::user();

        return [
            'form.description'  => 'required|string|max:255',
            'form.amount'       => 'required|numeric|min:0.01',
            'form.category'     => 'required|in:general,software,hardware,travel,labour,other',
            'form.vendor'       => 'nullable|string|max:100',
            'form.client_id'    => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query
                    ->where('company_id', Auth::user()->company_id)
                    ->whereNull('archived_at')
                    ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('id', $user->permittedClients()->select('clients.id')))),
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
        abort_if($e->client ? ! Auth::user()->canAccessClient($e->client) : Auth::user()->hasClientRestrictions(), 404);

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
        $user = Auth::user();

        if ($user->hasClientRestrictions() && empty($data['form']['client_id'])) {
            $this->addError('form.client_id', 'Select an accessible client.');
            return;
        }

        $payload = array_merge($data['form'], [
            'company_id' => $user->company_id,
            'user_id'    => Auth::id(),
            'client_id'  => $data['form']['client_id'] ?: null,
        ]);

        if ($this->editingId) {
            $expense = Expense::where('company_id', $user->company_id)->findOrFail($this->editingId);
            abort_if($expense->client ? ! $user->canAccessClient($expense->client) : $user->hasClientRestrictions(), 404);

            $before = AuditLogger::snapshot($expense);
            $expense->update($payload);
            AuditLogger::record('expense.updated', $expense, 'Expense updated.', $before, AuditLogger::snapshot($expense));
        } else {
            $expense = Expense::create($payload);
            AuditLogger::record('expense.created', $expense, 'Expense created.', null, AuditLogger::snapshot($expense));
        }
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $user = Auth::user();
        $expense = Expense::where('company_id', $user->company_id)->findOrFail($id);
        abort_if($expense->client ? ! $user->canAccessClient($expense->client) : $user->hasClientRestrictions(), 404);

        $before = AuditLogger::snapshot($expense);
        $expense->delete();
        AuditLogger::record('expense.deleted', $expense, 'Expense deleted.', $before, null);
    }

    public function render()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $expenses = Expense::where('company_id', $companyId)
            ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
            ->with(['client', 'user'])
            ->when($this->search,   fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->orderBy('expense_date', 'desc')
            ->paginate(25);

        return view('livewire.expenses.index', [
            'expenses'   => $expenses,
            'clients'    => Client::active()->where('company_id', $companyId)->visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'totalMonth' => Expense::where('company_id', $companyId)
                ->when($user->hasClientRestrictions(), fn ($q) => $q->whereIn('client_id', $user->permittedClients()->select('clients.id')))
                ->whereMonth('expense_date', today()->month)
                ->whereYear('expense_date', today()->year)
                ->sum('amount'),
        ])->layout('components.layouts.app', ['header' => 'Expenses']);
    }
}
