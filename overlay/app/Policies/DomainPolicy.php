<?php

namespace App\Policies;

class DomainPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view domains';

    protected string $managePermission = 'manage domains';
}
