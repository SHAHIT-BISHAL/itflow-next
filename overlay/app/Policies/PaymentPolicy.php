<?php

namespace App\Policies;

class PaymentPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view payments';

    protected string $managePermission = 'manage payments';
}
