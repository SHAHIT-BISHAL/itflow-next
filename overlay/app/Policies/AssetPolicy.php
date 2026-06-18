<?php

namespace App\Policies;

class AssetPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view assets';

    protected string $managePermission = 'manage assets';
}
