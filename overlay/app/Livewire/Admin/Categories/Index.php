<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $type = 'ticket';

    #[Validate('nullable|integer')]
    public ?int $parent_id = null;

    public string $color = '#6366f1';

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->parent_id = $category->parent_id;
        $this->color = $category->color ?? '#6366f1';

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['color'] = $this->color;

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
        } else {
            Category::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Category saved.');
    }

    public function delete(int $id): void
    {
        Category::findOrFail($id)->update(['archived_at' => now()]);
        session()->flash('success', 'Category archived.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'parent_id']);
        $this->type = 'ticket';
        $this->color = '#6366f1';
    }

    public function render()
    {
        return view('livewire.admin.categories.index', [
            'categories' => Category::active()->orderBy('type')->orderBy('sort_order')->orderBy('name')->get()->groupBy('type'),
            'parents' => Category::active()->get(),
        ])->layout('components.layouts.app', ['header' => 'Categories']);
    }
}
