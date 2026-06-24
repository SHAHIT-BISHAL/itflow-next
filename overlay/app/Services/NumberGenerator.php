<?php

namespace App\Services;

use App\Models\NumberingSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NumberGenerator
{
    /**
     * @return array<string, array{prefix: string, next_number: int, padding: int, suffix: string|null}>
     */
    public function defaults(): array
    {
        return [
            'ticket' => ['prefix' => 'TKT-', 'next_number' => 1, 'padding' => 4, 'suffix' => null],
            'invoice' => ['prefix' => 'INV-', 'next_number' => 1, 'padding' => 4, 'suffix' => null],
            'quote' => ['prefix' => 'QTE-', 'next_number' => 1, 'padding' => 4, 'suffix' => null],
            'project' => ['prefix' => 'PRJ-', 'next_number' => 1, 'padding' => 4, 'suffix' => null],
        ];
    }

    public function preview(int $companyId, string $type): string
    {
        return $this->setting($companyId, $type)->preview();
    }

    public function next(int $companyId, string $type): string
    {
        return DB::transaction(function () use ($companyId, $type) {
            $setting = $this->setting($companyId, $type, lock: true);
            $number = $setting->next_number;

            $setting->update(['next_number' => $number + 1]);

            return $setting->format($number);
        });
    }

    public function ensureDefaults(int $companyId): void
    {
        foreach (array_keys($this->defaults()) as $type) {
            $this->setting($companyId, $type);
        }
    }

    public function setting(int $companyId, string $type, bool $lock = false): NumberingSetting
    {
        $defaults = $this->defaults()[$type] ?? [
            'prefix' => strtoupper(substr($type, 0, 3)) . '-',
            'next_number' => 1,
            'padding' => 4,
            'suffix' => null,
        ];

        $query = NumberingSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', $type);

        if ($lock) {
            $query->lockForUpdate();
        }

        $setting = $query->first();

        if ($setting) {
            return $setting;
        }

        $defaults['next_number'] = $this->initialNextNumber(
            $companyId,
            $type,
            $defaults['prefix'],
            $defaults['next_number'],
        );

        return NumberingSetting::withoutGlobalScopes()->create(array_merge($defaults, [
            'company_id' => $companyId,
            'type' => $type,
        ]));
    }

    protected function initialNextNumber(int $companyId, string $type, string $prefix, int $fallback): int
    {
        $source = match ($type) {
            'invoice' => ['table' => 'invoices', 'column' => 'invoice_number'],
            'ticket' => ['table' => 'tickets', 'column' => 'ticket_number'],
            default => null,
        };

        if (! $source || ! Schema::hasTable($source['table']) || ! Schema::hasColumn($source['table'], $source['column'])) {
            return $fallback;
        }

        $max = DB::table($source['table'])
            ->where('company_id', $companyId)
            ->whereNotNull($source['column'])
            ->pluck($source['column'])
            ->map(function ($value) use ($prefix) {
                $value = (string) $value;

                if ($prefix && ! str_starts_with($value, $prefix)) {
                    return null;
                }

                preg_match('/(\d+)(?!.*\d)/', $value, $matches);

                return isset($matches[1]) ? (int) $matches[1] : null;
            })
            ->filter()
            ->max();

        return $max ? max($fallback, $max + 1) : $fallback;
    }
}
