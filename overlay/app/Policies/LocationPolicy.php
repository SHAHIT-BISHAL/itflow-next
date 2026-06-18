<?php

namespace App\Policies;

class LocationPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view clients';

    protected string $managePermission = 'manage clients';
}
