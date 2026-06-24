<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

abstract class TenantPolicy
{
    use HandlesAuthorization;

    protected string $viewPermission;

    protected string $managePermission;

    public function viewAny(User $user): bool
    {
        return $this->canViewModule($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->canViewModule($user) && $this->ownsRecord($user, $model);
    }

    public function create(User $user): bool
    {
        return $this->canManageModule($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->canManageModule($user) && $this->ownsRecord($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->canManageModule($user) && $this->ownsRecord($user, $model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->canManageModule($user) && $this->ownsRecord($user, $model);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->canManageModule($user) && $this->ownsRecord($user, $model);
    }

    protected function canViewModule(User $user): bool
    {
        return $this->hasPermission($user, $this->viewPermission)
            || $this->hasPermission($user, $this->managePermission);
    }

    protected function canManageModule(User $user): bool
    {
        return $this->hasPermission($user, $this->managePermission);
    }

    protected function ownsRecord(User $user, Model $model): bool
    {
        $companyId = $this->companyIdFor($model);

        if ($companyId !== null && $companyId !== (int) $user->company_id) {
            return false;
        }

        $client = $this->clientFor($model);

        if ($client) {
            return (int) $client->company_id === (int) $user->company_id
                && $user->canAccessClient($client);
        }

        return $companyId !== null && $companyId === (int) $user->company_id;
    }

    /**
     * @param  array<string, bool>  $visited
     */
    protected function clientFor(Model $model, array $visited = []): ?Client
    {
        $key = $this->visitKey($model);
        if (isset($visited[$key])) {
            return null;
        }
        $visited[$key] = true;

        if ($model instanceof Client) {
            return $model;
        }

        $clientId = $model->getAttribute('client_id');
        if ($clientId) {
            return Client::find($clientId);
        }

        foreach ($this->ownershipRelations() as $relation) {
            $related = $this->relatedModel($model, $relation);

            if ($related instanceof Client) {
                return $related;
            }

            if ($related instanceof Model) {
                $client = $this->clientFor($related, $visited);

                if ($client) {
                    return $client;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, bool>  $visited
     */
    protected function companyIdFor(Model $model, array $visited = []): ?int
    {
        $key = $this->visitKey($model);
        if (isset($visited[$key])) {
            return null;
        }
        $visited[$key] = true;

        $companyId = $model->getAttribute('company_id');
        if ($companyId !== null) {
            return (int) $companyId;
        }

        if ($model instanceof Client) {
            return (int) $model->company_id;
        }

        $client = $this->clientFor($model);
        if ($client) {
            return (int) $client->company_id;
        }

        foreach ($this->ownershipRelations() as $relation) {
            $related = $this->relatedModel($model, $relation);

            if ($related instanceof Model) {
                $companyId = $this->companyIdFor($related, $visited);

                if ($companyId !== null) {
                    return $companyId;
                }
            }
        }

        return null;
    }

    protected function relatedModel(Model $model, string $relation): ?Model
    {
        if (! method_exists($model, $relation)) {
            return null;
        }

        $related = $model->{$relation};

        return $related instanceof Model ? $related : null;
    }

    /**
     * @return array<int, string>
     */
    protected function ownershipRelations(): array
    {
        return [
            'client',
            'ticket',
            'reply',
            'invoice',
            'pipeline',
            'recurringInvoice',
            'password',
            'document',
            'deal',
            'contact',
            'location',
            'asset',
            'domain',
            'payment',
            'expense',
            'customField',
            'related',
            'customizable',
        ];
    }

    protected function visitKey(Model $model): string
    {
        return $model::class . ':' . ($model->getKey() ?? spl_object_id($model));
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Throwable) {
            return false;
        }
    }
}
