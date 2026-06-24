<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    protected $model = \App\Models\Domain::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'name' => $this->faker->unique()->domainName(),
            'registrar' => 'Namecheap',
            'expires_at' => now()->addYear()->toDateString(),
            'auto_renew' => true,
            'dns_provider' => 'Cloudflare',
            'ssl_expires_at' => now()->addMonths(3)->toDateString(),
            'ssl_issuer' => "Let's Encrypt",
            'notes' => null,
        ];
    }
}
