<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = \App\Models\Asset::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'location_id' => null,
            'name' => strtoupper($this->faker->bothify('SRV-###')),
            'asset_type' => 'Hardware',
            'manufacturer' => $this->faker->company(),
            'model' => $this->faker->word(),
            'serial_number' => $this->faker->bothify('SN-####'),
            'ip_address' => $this->faker->ipv4(),
            'mac_address' => $this->faker->macAddress(),
            'os' => 'Windows Server',
            'os_version' => '2022',
            'purchased_at' => now()->subYear()->toDateString(),
            'warranty_expires_at' => now()->addYear()->toDateString(),
            'notes' => null,
        ];
    }
}
