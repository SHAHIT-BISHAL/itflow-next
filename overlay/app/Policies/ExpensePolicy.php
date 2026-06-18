<?php

namespace App\Policies;

class ExpensePolicy extends TenantPolicy
{
    protected string $viewPermission = 'view expenses';

    protected string $managePermission = 'manage expenses';
}
