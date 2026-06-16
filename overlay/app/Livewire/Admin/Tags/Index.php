<?php

namespace App\Livewire\Admin\Tags;

use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:20')]
    public string $color = '#6366f1';

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $tag = Tag::findOrFail($id);

        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->color = $tag->color;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->where('company_id', auth()->user()->company_id)->ignore($this->editingId)],
            'color' => 'required|string|max:20',
        ]);

        if ($this->editingId) {
            Tag::findOrFail($this->editingId)->update($data);
        } else {
            Tag::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Tag saved.');
    }

    public function delete(int $id): void
    {
        Tag::findOrFail($id)->delete();
        session()->flash('success', 'Tag deleted.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name']);
        $this->color = '#6366f1';
    }

    public function render()
    {
        return view('livewire.admin.tags.index', [
            'tags' => Tag::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['header' => 'Tags']);
    }
}
