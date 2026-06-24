<?php

namespace App\Policies;

class CategoryPolicy extends TenantPolicy
{
    protected string $viewPermission = 'manage categories';

    protected string $managePermission = 'manage categories';
}
