<?php

namespace App\Policies;

use App\Models\Password;
use App\Models\User;

class PasswordPolicy extends TenantPolicy
{
    protected string $viewPermission = 'view passwords';

    protected string $managePermission = 'manage passwords';

    public function reveal(User $user, Password $password): bool
    {
        return $this->hasPermission($user, 'reveal passwords')
            && $this->ownsRecord($user, $password);
    }
}
