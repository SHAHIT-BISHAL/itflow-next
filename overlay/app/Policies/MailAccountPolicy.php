<?php

namespace App\Policies;

class MailAccountPolicy extends TenantPolicy
{
    protected string $viewPermission = 'manage mail accounts';

    protected string $managePermission = 'manage mail accounts';
}
