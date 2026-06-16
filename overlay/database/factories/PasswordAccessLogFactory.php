<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use App\Models\Password;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasswordAccessLogFactory extends Factory
{
    protected $model = \App\Models\PasswordAccessLog::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => Client::factory(),
            'password_id' => Password::factory(),
            'user_id' => User::factory(),
            'action' => 'reveal',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Factory',
            'accessed_at' => now(),
        ];
    }
}
