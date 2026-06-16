<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailAccountFactory extends Factory
{
    protected $model = \App\Models\MailAccount::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => 'Support Mailbox',
            'host' => 'imap.example.test',
            'port' => 993,
            'encryption' => 'ssl',
            'username' => $this->faker->safeEmail(),
            'password' => 'secret',
            'mailbox' => 'INBOX',
            'is_active' => true,
            'last_polled_at' => null,
        ];
    }
}
