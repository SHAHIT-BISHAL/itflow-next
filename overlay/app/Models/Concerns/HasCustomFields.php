<?php

namespace App\Models\Concerns;

use App\Models\CustomField;
use App\Models\CustomFieldValue;

trait HasCustomFields
{
    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'customizable');
    }

    public function availableCustomFields()
    {
        return CustomField::forModel(static::class)->get();
    }

    public function setCustomFieldValue(int $customFieldId, ?string $value): void
    {
        $this->customFieldValues()->updateOrCreate(
            ['custom_field_id' => $customFieldId],
            ['value' => $value],
        );
    }
}
