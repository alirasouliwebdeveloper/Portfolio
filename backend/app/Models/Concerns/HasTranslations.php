<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A translated row points `translation_of_id` at the row it translates; the original's own
 * `translation_of_id` stays null. `locale` defaults to 'en' (see the add_locale_support
 * migration and docs/02-architecture.md).
 */
trait HasTranslations
{
    public function scopeLocale(Builder $query, string $locale): Builder
    {
        return $query->where($query->qualifyColumn('locale'), $locale);
    }

    /** The row this one is a translation of, if it is one. */
    public function translationOf(): BelongsTo
    {
        return $this->belongsTo(static::class, 'translation_of_id');
    }

    /** Other locales of this row. */
    public function translations(): HasMany
    {
        return $this->hasMany(static::class, 'translation_of_id');
    }
}
