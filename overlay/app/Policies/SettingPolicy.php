<?php

namespace App\Policies;

class SettingPolicy extends TenantPolicy
{
    protected string $viewPermission = 'manage settings';

    protected string $managePermission = 'manage settings';
}
