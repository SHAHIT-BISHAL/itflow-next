<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\Deal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';
    public bool   $open  = false;

    public function updatedQuery(): void
    {
        $this->open = strlen($this->query) >= 2;
    }

    public function close(): void
    {
        $this->open  = false;
        $this->query = '';
    }

    public function render()
    {
        $results = ['clients' => [], 'tickets' => [], 'invoices' => [], 'deals' => []];

        if ($this->open) {
            $companyId = Auth::user()->company_id;
            $q         = $this->query;

            $results['clients'] = Client::where('company_id', $companyId)
                ->active()
                ->where('name', 'like', "%{$q}%")
                ->take(4)->get(['id', 'name']);

            $results['tickets'] = Ticket::where('company_id', $companyId)
                ->where('subject', 'like', "%{$q}%")
                ->take(4)->get(['id', 'subject', 'status', 'priority']);

            $results['invoices'] = Invoice::where('company_id', $companyId)
                ->active()
                ->where(fn ($sq) => $sq->where('invoice_number', 'like', "%{$q}%"))
                ->take(4)->get(['id', 'invoice_number', 'status', 'total']);

            $results['deals'] = Deal::where('company_id', $companyId)
                ->active()
                ->where('name', 'like', "%{$q}%")
                ->take(4)->get(['id', 'name', 'status']);
        }

        $hasResults = collect($results)->flatten()->isNotEmpty();

        return view('livewire.global-search', compact('results', 'hasResults'));
    }
}
