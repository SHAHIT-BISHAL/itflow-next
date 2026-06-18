<?php

namespace App\Livewire\Documents;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Document;
use App\Models\Domain;
use App\Models\Password;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

    #[Validate('required|string|max:50')]
    public string $document_type = 'general';

    #[Validate('nullable|string')]
    public ?string $content = null;

    #[Validate('nullable|date')]
    public ?string $review_due_at = null;

    /** @var array<int, int> */
    public array $assetIds = [];

    /** @var array<int, int> */
    public array $domainIds = [];

    /** @var array<int, int> */
    public array $passwordIds = [];

    public function mount(Client $client): void
    {
        abort_if(! Auth::user()->canAccessClient($client), 404);

        $this->client = $client;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->showViewer = false;
    }

    public function edit(int $id): void
    {
        $doc = Document::with('relations')->where('client_id', $this->client->id)->findOrFail($id);
        $this->editingId = $doc->id;
        $this->title = $doc->title;
        $this->document_type = $doc->document_type;
        $this->content = $doc->content;
        $this->review_due_at = $doc->review_due_at?->format('Y-m-d');
        $this->assetIds = $this->relatedIds($doc, Asset::class);
        $this->domainIds = $this->relatedIds($doc, Domain::class);
        $this->passwordIds = $this->relatedIds($doc, Password::class);
        $this->showModal = true;
        $this->showViewer = false;
    }

    public function view(int $id): void
    {
        $this->viewing = Document::with(['createdBy', 'reviewedBy', 'versions.createdBy', 'relations.related'])
            ->where('client_id', $this->client->id)
            ->findOrFail($id);
        $this->showViewer = true;
        $this->showModal = false;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['client_id'] = $this->client->id;

        DB::transaction(function () use ($data) {
            if ($this->editingId) {
                $document = Document::where('client_id', $this->client->id)->findOrFail($this->editingId);
                $before = AuditLogger::snapshot($document);
                $shouldVersion = $document->title !== $data['title']
                    || $document->content !== ($data['content'] ?? null);

                $document->update($data);
                AuditLogger::record('document.updated', $document, 'Document updated.', $before, AuditLogger::snapshot($document));

                if ($shouldVersion) {
                    $this->createVersion($document, 'Document updated');
                }
            } else {
                $data['created_by'] = Auth::id();
                $document = Document::create($data);
                $this->createVersion($document, 'Initial version');
                AuditLogger::record('document.created', $document, 'Document created.', null, AuditLogger::snapshot($document));
            }

            $this->syncRelations($document);
        });

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $document = Document::where('client_id', $this->client->id)->findOrFail($id);
        $before = AuditLogger::snapshot($document);
        $document->update(['archived_at' => now()]);
        AuditLogger::record('document.archived', $document, 'Document archived.', $before, AuditLogger::snapshot($document));
    }

    public function markReviewed(int $id): void
    {
        $document = Document::where('client_id', $this->client->id)->findOrFail($id);
        $before = AuditLogger::snapshot($document);
        $document->update([
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
            'review_due_at' => now()->addMonths(6)->toDateString(),
        ]);
        AuditLogger::record('document.reviewed', $document, 'Document reviewed.', $before, AuditLogger::snapshot($document));

        $this->view($document->id);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
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
            ->withCount(['versions', 'relations'])
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.documents.manager', [
            'documents' => $documents,
            'assets' => Asset::where('client_id', $this->client->id)->active()->orderBy('name')->get(),
            'domains' => Domain::where('client_id', $this->client->id)->active()->orderBy('name')->get(),
            'passwords' => Password::where('client_id', $this->client->id)->active()->orderBy('name')->get(),
        ]);
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'content', 'review_due_at',
            'assetIds', 'domainIds', 'passwordIds',
        ]);
        $this->document_type = 'general';
    }

    protected function createVersion(Document $document, string $summary): void
    {
        $document->versions()->create([
            'created_by' => Auth::id(),
            'version_number' => $document->versions()->max('version_number') + 1,
            'title' => $document->title,
            'content' => $document->content,
            'change_summary' => $summary,
        ]);
    }

    protected function syncRelations(Document $document): void
    {
        $document->relations()->delete();

        $this->attachRelations($document, Asset::class, $this->validRelatedIds(Asset::class, $this->assetIds));
        $this->attachRelations($document, Domain::class, $this->validRelatedIds(Domain::class, $this->domainIds));
        $this->attachRelations($document, Password::class, $this->validRelatedIds(Password::class, $this->passwordIds));
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, int|string>  $ids
     * @return array<int, int>
     */
    protected function validRelatedIds(string $modelClass, array $ids): array
    {
        return $modelClass::query()
            ->where('client_id', $this->client->id)
            ->whereIn('id', array_filter($ids))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, int>  $ids
     */
    protected function attachRelations(Document $document, string $modelClass, array $ids): void
    {
        foreach ($ids as $id) {
            $document->relations()->create([
                'related_type' => $modelClass,
                'related_id' => $id,
                'relationship_type' => 'reference',
            ]);
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<int, int>
     */
    protected function relatedIds(Document $document, string $modelClass): array
    {
        return $document->relations
            ->where('related_type', $modelClass)
            ->pluck('related_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
