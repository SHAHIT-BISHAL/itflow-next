<?php

namespace App\Livewire\Deals;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Deal $deal;

    public string $editStage  = '';
    public string $editStatus = '';
    public string $editAssignee = '';

    // Activity form
    public bool  $showActivityModal = false;
    public array $activityForm = [
        'type'        => 'note',
        'subject'     => '',
        'description' => '',
        'due_at'      => '',
        'outcome'     => '',
    ];

    protected array $activityRules = [
        'activityForm.type'        => 'required|in:call,email,meeting,task,note',
        'activityForm.subject'     => 'required|string|max:255',
        'activityForm.description' => 'nullable|string',
        'activityForm.due_at'      => 'nullable|date',
        'activityForm.outcome'     => 'nullable|string',
    ];

    public function mount(Deal $deal): void
    {
        $this->deal        = $deal;
        $this->editStage   = (string) $deal->stage_id;
        $this->editStatus  = $deal->status;
        $this->editAssignee = (string) ($deal->assigned_to ?? '');
    }

    public function updateMeta(): void
    {
        $this->deal->update([
            'stage_id'    => $this->editStage,
            'status'      => $this->editStatus,
            'assigned_to' => $this->editAssignee ?: null,
            'closed_at'   => in_array($this->editStatus, ['won', 'lost']) ? ($this->deal->closed_at ?? now()) : null,
        ]);
        $this->deal->refresh();
        session()->flash('success', 'Deal updated.');
    }

    public function openActivityModal(): void
    {
        $this->activityForm = ['type' => 'note', 'subject' => '', 'description' => '', 'due_at' => '', 'outcome' => ''];
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        $data = $this->validate($this->activityRules);
        Activity::create(array_merge($data['activityForm'], [
            'company_id' => Auth::user()->company_id,
            'user_id'    => Auth::id(),
            'deal_id'    => $this->deal->id,
            'client_id'  => $this->deal->client_id,
            'contact_id' => $this->deal->contact_id,
            'due_at'     => $data['activityForm']['due_at'] ?: null,
        ]));
        $this->showActivityModal = false;
        $this->deal->refresh();
    }

    public function completeActivity(int $id): void
    {
        Activity::findOrFail($id)->update(['completed_at' => now()]);
        $this->deal->refresh();
    }

    public function render()
    {
        return view('livewire.deals.show', [
            'stages'     => $this->deal->pipeline->stages,
            'users'      => User::orderBy('name')->get(['id', 'name']),
            'activities' => $this->deal->activities()->with('user')->get(),
        ])->layout('components.layouts.app', ['header' => $this->deal->name]);
    }
}
