<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $metadata
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $before = null,
        ?array $after = null,
        array $metadata = [],
    ): AuditLog {
        $actor = Auth::user();
        $actorModel = $actor instanceof Model ? $actor : null;

        return AuditLog::create([
            'company_id' => self::companyId($subject, $actorModel, $metadata),
            'actor_type' => $actorModel ? $actorModel::class : null,
            'actor_id' => $actorModel?->getKey(),
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'before' => $before,
            'after' => $after,
            'metadata' => $metadata ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => str(request()?->userAgent() ?? '')->limit(1000)->toString(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function snapshot(Model $model): array
    {
        return Arr::except($model->getAttributes(), [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected static function companyId(?Model $subject, ?Model $actor, array $metadata): ?int
    {
        if (isset($metadata['company_id'])) {
            return (int) $metadata['company_id'];
        }

        $subjectCompanyId = self::companyIdFor($subject);
        if ($subjectCompanyId !== null) {
            return $subjectCompanyId;
        }

        if ($actor && $actor->getAttribute('company_id')) {
            return (int) $actor->getAttribute('company_id');
        }

        return null;
    }

    /**
     * @param  array<string, bool>  $visited
     */
    protected static function companyIdFor(?Model $model, array $visited = []): ?int
    {
        if (! $model) {
            return null;
        }

        $key = self::visitKey($model);
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

        $clientId = $model->getAttribute('client_id');
        if ($clientId) {
            $client = Client::find($clientId);

            if ($client) {
                return (int) $client->company_id;
            }
        }

        foreach (self::ownershipRelations() as $relation) {
            $related = self::relatedModel($model, $relation);

            if (! $related) {
                continue;
            }

            $companyId = self::companyIdFor($related, $visited);

            if ($companyId !== null) {
                return $companyId;
            }
        }

        return null;
    }

    protected static function relatedModel(Model $model, string $relation): ?Model
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
    protected static function ownershipRelations(): array
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

    protected static function visitKey(Model $model): string
    {
        return $model::class . ':' . ($model->getKey() ?? spl_object_id($model));
    }
}
