<?php

namespace Database\Factories;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldValueFactory extends Factory
{
    protected $model = \App\Models\CustomFieldValue::class;

    public function definition(): array
    {
        return [
            'custom_field_id' => CustomField::factory(),
            'customizable_type' => \App\Models\Client::class,
            'customizable_id' => \App\Models\Client::factory(),
            'value' => $this->faker->word(),
        ];
    }
}
