<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanySettingFactory extends Factory
{
    protected $model = \App\Models\CompanySetting::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'timezone' => 'UTC',
            'default_currency' => 'USD',
            'tax_rate' => 0,
            'default_net_terms' => 30,
            'ticket_sla_hours' => 24,
            'business_hours' => null,
            'email_from_name' => $this->faker->company(),
            'email_from_address' => $this->faker->companyEmail(),
            'portal_name' => $this->faker->company(),
            'portal_url' => null,
        ];
    }
}
