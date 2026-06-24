<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $action
 * @property string|null $description
 * @property array<string, mixed>|null $before
 * @property array<string, mixed>|null $after
 * @property array<string, mixed>|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|null $actor
 * @property-read Model|null $subject
 */
class AuditLog extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'actor_type', 'actor_id', 'subject_type', 'subject_id',
        'action', 'description', 'before', 'after', 'metadata',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'metadata' => 'array',
    ];

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
