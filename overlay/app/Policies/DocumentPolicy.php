<?php

namespace App\Policies;

class DocumentPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view documents';

    protected string $managePermission = 'manage documents';
}
