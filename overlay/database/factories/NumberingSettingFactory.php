<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class NumberingSettingFactory extends Factory
{
    protected $model = \App\Models\NumberingSetting::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => 'ticket',
            'prefix' => 'TKT-',
            'next_number' => 1,
            'padding' => 4,
            'suffix' => null,
        ];
    }
}
