<?php

namespace App\Livewire\Deals;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

    protected function rules(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'form.name'                => 'required|string|max:255',
            'form.client_id'           => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->whereNull('archived_at')),
            ],
            'form.contact_id'          => [
                'nullable',
                Rule::exists('contacts', 'id')->where(fn ($query) => $query
                    ->where('client_id', $this->form['client_id'] ?: 0)
                    ->whereNull('archived_at')),
            ],
            'form.pipeline_id'         => [
                'required',
                Rule::exists('pipelines', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'form.stage_id'            => [
                'required',
                Rule::exists('pipeline_stages', 'id')->where(fn ($query) => $query
                    ->where('pipeline_id', $this->form['pipeline_id'] ?: 0)),
            ],
            'form.assigned_to'         => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->whereNull('archived_at')),
            ],
            'form.value'               => 'nullable|numeric|min:0',
            'form.expected_close_date' => 'nullable|date',
            'form.notes'               => 'nullable|string',
        ];
    }

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
        $this->stageOptions = Pipeline::where('company_id', Auth::user()->company_id)->find($this->form['pipeline_id'])
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
            'expected_close_date' => $data['form']['expected_close_date'] ?: null,
        ]));
        $this->showModal = false;
    }

    public function moveStage(int $dealId, int $stageId): void
    {
        $stage = PipelineStage::whereHas('pipeline', fn ($query) => $query->where('company_id', Auth::user()->company_id))
            ->findOrFail($stageId);

        Deal::where('company_id', Auth::user()->company_id)
            ->where('pipeline_id', $stage->pipeline_id)
            ->findOrFail($dealId)
            ->update(['stage_id' => $stage->id]);
    }

    public function markWon(int $dealId): void
    {
        Deal::where('company_id', Auth::user()->company_id)->findOrFail($dealId)->update(['status' => 'won', 'closed_at' => now()]);
    }

    public function markLost(int $dealId, string $reason = ''): void
    {
        Deal::where('company_id', Auth::user()->company_id)->findOrFail($dealId)->update(['status' => 'lost', 'closed_at' => now(), 'lost_reason' => $reason]);
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
            'clients'        => Client::active()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'users'          => User::active()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'totalValue'     => Deal::active()->where('company_id', $companyId)->open()->sum('value'),
            'openCount'      => Deal::active()->where('company_id', $companyId)->open()->count(),
        ])->layout('components.layouts.app', ['header' => 'Deals']);
    }
}
