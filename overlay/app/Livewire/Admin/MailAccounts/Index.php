<?php

namespace App\Livewire\Admin\MailAccounts;

use App\Models\MailAccount;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public bool  $showModal  = false;
    public ?int  $editingId  = null;

    public array $form = [
        'name'       => '',
        'host'       => '',
        'port'       => 993,
        'encryption' => 'ssl',
        'username'   => '',
        'password'   => '',
        'mailbox'    => 'INBOX',
        'is_active'  => true,
    ];

    protected array $rules = [
        'form.name'       => 'required|string|max:100',
        'form.host'       => 'required|string|max:255',
        'form.port'       => 'required|integer|min:1|max:65535',
        'form.encryption' => 'required|in:ssl,tls,none',
        'form.username'   => 'required|string|max:255',
        'form.password'   => 'nullable|string',
        'form.mailbox'    => 'required|string|max:100',
        'form.is_active'  => 'boolean',
    ];

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = ['name' => '', 'host' => '', 'port' => 993, 'encryption' => 'ssl', 'username' => '', 'password' => '', 'mailbox' => 'INBOX', 'is_active' => true];
        $this->showModal = true;
    }

    public function openEdit(MailAccount $account): void
    {
        $this->editingId = $account->id;
        $this->form = [
            'name'       => $account->name,
            'host'       => $account->host,
            'port'       => $account->port,
            'encryption' => $account->encryption,
            'username'   => $account->username,
            'password'   => '',
            'mailbox'    => $account->mailbox,
            'is_active'  => $account->is_active,
        ];
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $companyId = Auth::user()->company_id;

        if ($this->editingId) {
            $account = MailAccount::findOrFail($this->editingId);
            $update = collect($data['form'])->except('password')->toArray();
            if (! empty($data['form']['password'])) {
                $update['password'] = $data['form']['password'];
            }
            $account->update($update);
        } else {
            MailAccount::create(array_merge($data['form'], ['company_id' => $companyId]));
        }

        $this->showModal = false;
    }

    public function delete(MailAccount $account): void
    {
        $account->delete();
    }

    public function toggleActive(MailAccount $account): void
    {
        $account->update(['is_active' => ! $account->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.mail-accounts.index', [
            'accounts' => MailAccount::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['header' => 'Mail Accounts']);
    }
}
