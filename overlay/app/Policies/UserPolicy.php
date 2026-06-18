<?php

namespace App\Policies;

class UserPolicy extends TenantPolicy
{
    protected string $viewPermission = 'manage users';

    protected string $managePermission = 'manage users';
}
