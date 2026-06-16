<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Manager extends Component
{
    public Client $client;

    public bool $showModal = false;
    public bool $showViewer = false;
    public ?int $editingId = null;
    public ?Document $viewing = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string')]
    public ?string $content = null;

    public function create(): void
    {
        $this->reset(['editingId', 'title', 'content']);
        $this->showModal = true;
        $this->showViewer = false;
    }

    public function edit(int $id): void
    {
        $doc = Document::findOrFail($id);
        $this->editingId = $doc->id;
        $this->title = $doc->title;
        $this->content = $doc->content;
        $this->showModal = true;
        $this->showViewer = false;
    }

    public function view(int $id): void
    {
        $this->viewing = Document::findOrFail($id);
        $this->showViewer = true;
        $this->showModal = false;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;
        $data['created_by'] = Auth::id();

        if ($this->editingId) {
            Document::findOrFail($this->editingId)->update($data);
        } else {
            Document::create($data);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        Document::findOrFail($id)->update(['archived_at' => now()]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'title', 'content']);
        $this->resetValidation();
    }

    public function closeViewer(): void
    {
        $this->showViewer = false;
        $this->viewing = null;
    }

    public function render()
    {
        $documents = Document::query()
            ->where('client_id', $this->client->id)
            ->active()
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.documents.manager', ['documents' => $documents]);
    }
}
