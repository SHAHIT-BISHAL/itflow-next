<?php

namespace App\Policies;

class DealPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view deals';

    protected string $managePermission = 'manage deals';
}
