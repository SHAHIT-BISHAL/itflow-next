<?php

namespace App\Policies;

class AuditLogPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view audit logs';

    protected string $managePermission = 'manage settings';
}
