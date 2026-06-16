<?php

namespace App\Models\Concerns;

use App\Models\Tag;

trait HasTags
{
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function syncTags(array $tagIds): void
    {
        $this->tags()->sync($tagIds);
    }
}
