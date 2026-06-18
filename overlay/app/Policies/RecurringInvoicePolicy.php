<?php

namespace App\Policies;

class RecurringInvoicePolicy extends TenantPolicy
{
    protected string $viewPermission = 'view invoices';

    protected string $managePermission = 'manage invoices';
}
