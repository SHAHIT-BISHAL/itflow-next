<?php

namespace App\Policies;

class TagPolicy extends TenantPolicy
{
    protected string $viewPermission = 'manage tags';

    protected string $managePermission = 'manage tags';
}
