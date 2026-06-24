<?php

namespace App\Policies;

class TicketPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view tickets';

    protected string $managePermission = 'manage tickets';
}
