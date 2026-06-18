<?php

namespace App\Policies;

class PipelinePolicy extends TenantPolicy
{
    protected string $viewPermission = 'view deals';

    protected string $managePermission = 'manage pipelines';
}
