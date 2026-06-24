<?php

namespace App\Policies;

class ContactPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view clients';

    protected string $managePermission = 'manage clients';
}
