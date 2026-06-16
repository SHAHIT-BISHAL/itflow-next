<?php

namespace App\Livewire\Admin\Pipelines;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public ?int  $editingPipelineId = null;
    public bool  $showPipelineModal = false;
    public array $pipelineForm = ['name' => '', 'is_default' => false];

    public ?Pipeline $activePipeline = null;
    public bool  $showStageModal = false;
    public ?int  $editingStageId = null;
    public array $stageForm = ['name' => '', 'color' => 'blue', 'probability' => 20];

    public function openPipeline(?int $id = null): void
    {
        $this->editingPipelineId = $id;
        if ($id) {
            $p = Pipeline::findOrFail($id);
            $this->pipelineForm = ['name' => $p->name, 'is_default' => $p->is_default];
        } else {
            $this->pipelineForm = ['name' => '', 'is_default' => false];
        }
        $this->showPipelineModal = true;
    }

    public function savePipeline(): void
    {
        $this->validate(['pipelineForm.name' => 'required|string|max:100']);
        $companyId = Auth::user()->company_id;

        if ($this->pipelineForm['is_default']) {
            Pipeline::where('company_id', $companyId)->update(['is_default' => false]);
        }

        if ($this->editingPipelineId) {
            Pipeline::findOrFail($this->editingPipelineId)->update($this->pipelineForm);
        } else {
            $pipeline = Pipeline::create(array_merge($this->pipelineForm, ['company_id' => $companyId]));
            // Seed default stages
            foreach (['Prospecting', 'Qualified', 'Proposal', 'Negotiation', 'Closed Won'] as $i => $name) {
                $pipeline->stages()->create(['name' => $name, 'probability' => ($i + 1) * 20, 'sort_order' => $i]);
            }
        }
        $this->showPipelineModal = false;
    }

    public function deletePipeline(int $id): void
    {
        Pipeline::findOrFail($id)->delete();
        if ($this->activePipeline?->id === $id) $this->activePipeline = null;
    }

    public function selectPipeline(int $id): void
    {
        $this->activePipeline = Pipeline::with('stages')->findOrFail($id);
    }

    public function openStage(?int $id = null): void
    {
        $this->editingStageId = $id;
        if ($id) {
            $s = PipelineStage::findOrFail($id);
            $this->stageForm = ['name' => $s->name, 'color' => $s->color, 'probability' => $s->probability];
        } else {
            $this->stageForm = ['name' => '', 'color' => 'blue', 'probability' => 20];
        }
        $this->showStageModal = true;
    }

    public function saveStage(): void
    {
        $this->validate(['stageForm.name' => 'required|string|max:100', 'stageForm.probability' => 'required|integer|min:0|max:100']);
        if ($this->editingStageId) {
            PipelineStage::findOrFail($this->editingStageId)->update($this->stageForm);
        } else {
            $this->activePipeline->stages()->create(array_merge($this->stageForm, [
                'sort_order' => $this->activePipeline->stages->count(),
            ]));
        }
        $this->activePipeline->refresh();
        $this->showStageModal = false;
    }

    public function deleteStage(int $id): void
    {
        PipelineStage::findOrFail($id)->delete();
        $this->activePipeline->refresh();
    }

    public function render()
    {
        $pipelines = Pipeline::where('company_id', Auth::user()->company_id)
            ->withCount('deals')
            ->orderBy('sort_order')
            ->get();

        if (! $this->activePipeline && $pipelines->isNotEmpty()) {
            $this->activePipeline = Pipeline::with('stages')->find($pipelines->first()->id);
        }

        return view('livewire.admin.pipelines.index', compact('pipelines'))
            ->layout('components.layouts.app', ['header' => 'Pipelines']);
    }
}
