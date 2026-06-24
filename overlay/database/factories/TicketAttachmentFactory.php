<?php

namespace Database\Factories;

use App\Models\TicketReply;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketAttachmentFactory extends Factory
{
    protected $model = \App\Models\TicketAttachment::class;

    public function definition(): array
    {
        return [
            'ticket_reply_id' => TicketReply::factory(),
            'filename' => 'support.txt',
            'path' => 'attachments/support.txt',
            'mime_type' => 'text/plain',
            'size' => 128,
        ];
    }
}
