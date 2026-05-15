<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionType extends Model
{
    protected $fillable = [
        'slug', 'label', 'icon', 'gradient', 'badge_class',
        'border_class', 'bg_class', 'behavior', 'is_system', 'is_active', 'order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /** In-memory map: slug => SectionType (memoized per request) */
    protected static ?\Illuminate\Support\Collection $cachedMap = null;

    public static function map(): \Illuminate\Support\Collection
    {
        if (static::$cachedMap === null) {
            static::$cachedMap = static::orderBy('order')->orderBy('id')->get()->keyBy('slug');
        }
        return static::$cachedMap;
    }

    public static function activeOptions(): \Illuminate\Support\Collection
    {
        return static::map()->where('is_active', true)->values();
    }

    public static function flushCache(): void
    {
        static::$cachedMap = null;
        cache()->forget('section_types_map');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
