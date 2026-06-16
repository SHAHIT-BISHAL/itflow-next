<?php

namespace App\Livewire\Deals;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $view     = 'list'; // list | kanban
    public string $search   = '';
    public string $status   = 'open';
    public string $assignee = '';
    public int    $pipelineId = 0;

    public bool  $showModal = false;
    public array $form = [
        'name'                => '',
        'client_id'           => '',
        'contact_id'          => '',
        'pipeline_id'         => '',
        'stage_id'            => '',
        'assigned_to'         => '',
        'value'               => '',
        'expected_close_date' => '',
        'notes'               => '',
    ];

    public array $stageOptions = [];

    protected array $rules = [
        'form.name'                => 'required|string|max:255',
        'form.client_id'           => 'nullable|exists:clients,id',
        'form.contact_id'          => 'nullable|exists:contacts,id',
        'form.pipeline_id'         => 'required|exists:pipelines,id',
        'form.stage_id'            => 'required|exists:pipeline_stages,id',
        'form.assigned_to'         => 'nullable|exists:users,id',
        'form.value'               => 'nullable|numeric|min:0',
        'form.expected_close_date' => 'nullable|date',
        'form.notes'               => 'nullable|string',
    ];

    public function mount(): void
    {
        $default = Pipeline::where('company_id', Auth::user()->company_id)->where('is_default', true)->first()
            ?? Pipeline::where('company_id', Auth::user()->company_id)->first();

        if ($default) {
            $this->form['pipeline_id'] = $default->id;
            $this->pipelineId          = $default->id;
            $this->loadStages();
        }
    }

    public function updatedFormPipelineId(): void { $this->loadStages(); $this->form['stage_id'] = ''; }

    public function loadStages(): void
    {
        $this->stageOptions = Pipeline::find($this->form['pipeline_id'])
            ?->stages->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->toArray() ?? [];
    }

    public function openModal(): void
    {
        $this->form = array_merge($this->form, ['name' => '', 'client_id' => '', 'contact_id' => '', 'assigned_to' => '', 'value' => '', 'expected_close_date' => '', 'notes' => '']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        Deal::create(array_merge($data['form'], [
            'company_id' => Auth::user()->company_id,
            'status'     => 'open',
            'value'      => $data['form']['value'] ?: 0,
            'client_id'  => $data['form']['client_id'] ?: null,
            'contact_id' => $data['form']['contact_id'] ?: null,
            'assigned_to' => $data['form']['assigned_to'] ?: null,
        ]));
        $this->showModal = false;
    }

    public function moveStage(int $dealId, int $stageId): void
    {
        Deal::findOrFail($dealId)->update(['stage_id' => $stageId]);
    }

    public function markWon(int $dealId): void
    {
        Deal::findOrFail($dealId)->update(['status' => 'won', 'closed_at' => now()]);
    }

    public function markLost(int $dealId, string $reason = ''): void
    {
        Deal::findOrFail($dealId)->update(['status' => 'lost', 'closed_at' => now(), 'lost_reason' => $reason]);
    }

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingStatus(): void  { $this->resetPage(); }

    public function render()
    {
        $companyId = Auth::user()->company_id;
        $pipelines = Pipeline::where('company_id', $companyId)->with('stages')->orderBy('sort_order')->get();
        $activePipeline = $pipelines->firstWhere('id', $this->pipelineId) ?? $pipelines->first();

        $dealsQuery = Deal::active()
            ->where('company_id', $companyId)
            ->with(['client', 'stage', 'assignee'])
            ->when($this->search,   fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->status,   fn ($q) => $q->where('status', $this->status))
            ->when($this->assignee, fn ($q) => $q->where('assigned_to', $this->assignee));

        // For kanban, group by stage
        $kanbanDeals = [];
        if ($this->view === 'kanban' && $activePipeline) {
            $kanbanDeals = $dealsQuery->where('pipeline_id', $activePipeline->id)
                ->get()
                ->groupBy('stage_id');
        }

        return view('livewire.deals.index', [
            'deals'          => $this->view === 'list' ? $dealsQuery->latest()->paginate(25) : collect(),
            'pipelines'      => $pipelines,
            'activePipeline' => $activePipeline,
            'kanbanDeals'    => $kanbanDeals,
            'clients'        => Client::active()->orderBy('name')->get(['id', 'name']),
            'users'          => User::orderBy('name')->get(['id', 'name']),
            'totalValue'     => Deal::active()->where('company_id', $companyId)->open()->sum('value'),
            'openCount'      => Deal::active()->where('company_id', $companyId)->open()->count(),
        ])->layout('components.layouts.app', ['header' => 'Deals']);
    }
}
