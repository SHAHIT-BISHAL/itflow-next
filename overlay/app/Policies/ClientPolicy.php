<?php

namespace App\Policies;

class ClientPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view clients';

    protected string $managePermission = 'manage clients';
}
