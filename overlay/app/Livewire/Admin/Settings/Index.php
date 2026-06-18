<?php

namespace App\Livewire\Admin\Settings;

use App\Models\CompanySetting;
use App\Models\NumberingSetting;
use App\Services\AuditLogger;
use App\Services\NumberGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public string $companyName = '';
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $website = null;
    public ?string $address = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;
    public ?string $country = null;

    public string $timezone = 'UTC';
    public string $defaultCurrency = 'USD';
    public string $taxRate = '0.00';
    public int $defaultNetTerms = 30;
    public int $ticketSlaHours = 24;
    public ?string $emailFromName = null;
    public ?string $emailFromAddress = null;
    public ?string $portalName = null;
    public ?string $portalUrl = null;

    /**
     * @var array<string, array{label: string, prefix: string, next_number: int, padding: int, suffix: string|null, preview: string}>
     */
    public array $numbering = [];

    public function mount(NumberGenerator $numbers): void
    {
        $company = Auth::user()->company;
        $numbers->ensureDefaults($company->id);

        $settings = $company->settings()->firstOrCreate([], [
            'timezone' => $company->timezone,
            'default_currency' => $company->currency,
            'email_from_name' => $company->name,
            'email_from_address' => $company->email,
            'portal_name' => $company->name,
        ]);

        $this->companyName = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->website = $company->website;
        $this->address = $company->address;
        $this->city = $company->city;
        $this->state = $company->state;
        $this->zip = $company->zip;
        $this->country = $company->country;

        $this->timezone = $settings->timezone;
        $this->defaultCurrency = $settings->default_currency;
        $this->taxRate = (string) $settings->tax_rate;
        $this->defaultNetTerms = $settings->default_net_terms;
        $this->ticketSlaHours = $settings->ticket_sla_hours;
        $this->emailFromName = $settings->email_from_name;
        $this->emailFromAddress = $settings->email_from_address;
        $this->portalName = $settings->portal_name;
        $this->portalUrl = $settings->portal_url;

        $this->loadNumbering();
    }

    public function saveCompanySettings(): void
    {
        $data = $this->validate($this->companyRules());
        $company = Auth::user()->company;
        $settings = $this->settings();

        $companyBefore = AuditLogger::snapshot($company);
        $settingsBefore = AuditLogger::snapshot($settings);

        $company->update([
            'name' => $data['companyName'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'website' => $data['website'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $data['country'],
            'timezone' => $data['timezone'],
            'currency' => $data['defaultCurrency'],
        ]);

        $settings->update([
            'timezone' => $data['timezone'],
            'default_currency' => $data['defaultCurrency'],
            'tax_rate' => $data['taxRate'],
            'default_net_terms' => $data['defaultNetTerms'],
            'ticket_sla_hours' => $data['ticketSlaHours'],
            'email_from_name' => $data['emailFromName'],
            'email_from_address' => $data['emailFromAddress'],
            'portal_name' => $data['portalName'],
            'portal_url' => $data['portalUrl'],
        ]);

        AuditLogger::record('company.updated', $company, 'Company profile updated.', $companyBefore, AuditLogger::snapshot($company));
        AuditLogger::record('settings.updated', $settings, 'Company settings updated.', $settingsBefore, AuditLogger::snapshot($settings));

        session()->flash('success', 'Company settings saved.');
    }

    public function saveNumbering(): void
    {
        $data = $this->validate($this->numberingRules());
        $companyId = Auth::user()->company_id;

        foreach ($data['numbering'] as $type => $row) {
            $setting = NumberingSetting::where('company_id', $companyId)->where('type', $type)->firstOrFail();
            $before = AuditLogger::snapshot($setting);

            $setting->update([
                'prefix' => $row['prefix'] ?? '',
                'next_number' => $row['next_number'],
                'padding' => $row['padding'],
                'suffix' => $row['suffix'] ?: null,
            ]);

            AuditLogger::record('numbering.updated', $setting, ucfirst($type) . ' numbering updated.', $before, AuditLogger::snapshot($setting));
        }

        $this->loadNumbering();
        session()->flash('success', 'Numbering settings saved.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function companyRules(): array
    {
        return [
            'companyName' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:100',
            'timezone' => ['required', 'string', 'max:100', Rule::in(timezone_identifiers_list())],
            'defaultCurrency' => 'required|string|size:3',
            'taxRate' => 'required|numeric|min:0|max:100',
            'defaultNetTerms' => 'required|integer|min:0|max:365',
            'ticketSlaHours' => 'required|integer|min:1|max:8760',
            'emailFromName' => 'nullable|string|max:255',
            'emailFromAddress' => 'nullable|email|max:255',
            'portalName' => 'nullable|string|max:255',
            'portalUrl' => 'nullable|url|max:255',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function numberingRules(): array
    {
        return [
            'numbering' => 'required|array',
            'numbering.*.prefix' => 'nullable|string|max:20',
            'numbering.*.next_number' => 'required|integer|min:1|max:999999999',
            'numbering.*.padding' => 'required|integer|min:1|max:12',
            'numbering.*.suffix' => 'nullable|string|max:20',
        ];
    }

    protected function settings(): CompanySetting
    {
        $company = Auth::user()->company;

        return $company->settings()->firstOrCreate([], [
            'timezone' => $company->timezone,
            'default_currency' => $company->currency,
        ]);
    }

    protected function loadNumbering(): void
    {
        $labels = [
            'ticket' => 'Tickets',
            'invoice' => 'Invoices',
            'quote' => 'Quotes',
            'project' => 'Projects',
        ];

        $sortOrder = array_flip(array_keys($labels));

        $this->numbering = NumberingSetting::where('company_id', Auth::user()->company_id)
            ->whereIn('type', array_keys($labels))
            ->get()
            ->sortBy(fn (NumberingSetting $setting) => $sortOrder[$setting->type] ?? 99)
            ->mapWithKeys(fn (NumberingSetting $setting) => [
                $setting->type => [
                    'label' => $labels[$setting->type] ?? ucfirst($setting->type),
                    'prefix' => $setting->prefix,
                    'next_number' => $setting->next_number,
                    'padding' => $setting->padding,
                    'suffix' => $setting->suffix,
                    'preview' => $setting->preview(),
                ],
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.settings.index', [
            'timezones' => timezone_identifiers_list(),
        ])->layout('components.layouts.app', ['header' => 'Company Settings']);
    }
}
