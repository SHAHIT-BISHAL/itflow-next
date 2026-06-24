<?php

namespace App\Policies;

class InvoicePolicy extends TenantPolicy
{
    protected string $viewPermission = 'view invoices';

    protected string $managePermission = 'manage invoices';
}
