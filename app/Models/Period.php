<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = ['label', 'type', 'year', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Generate all periods for a given year and type.
     * Skips labels that already exist. Returns count of created records.
     */
    public static function generateForYear(int $year, string $type): int
    {
        $suffixes = match ($type) {
            'trimestral' => ['T1', 'T2', 'T3', 'T4'],
            'semestral'  => ['S1', 'S2'],
            'anual'      => [],
            default      => [],
        };

        $created  = 0;
        $maxOrder = static::where('year', $year)->max('sort_order') ?? 0;

        if ($type === 'anual') {
            $label = (string) $year;
            if (! static::where('label', $label)->exists()) {
                static::create([
                    'label'      => $label,
                    'type'       => $type,
                    'year'       => $year,
                    'sort_order' => $maxOrder + 1,
                    'is_active'  => true,
                ]);
                $created++;
            }
        } else {
            foreach ($suffixes as $i => $s) {
                $label = "{$year}-{$s}";
                if (! static::where('label', $label)->exists()) {
                    static::create([
                        'label'      => $label,
                        'type'       => $type,
                        'year'       => $year,
                        'sort_order' => $maxOrder + $i + 1,
                        'is_active'  => true,
                    ]);
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * All active periods grouped by year (newest first).
     */
    public static function activeGroupedByYear(): \Illuminate\Support\Collection
    {
        return static::where('is_active', true)
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('year');
    }

    /**
     * All periods (including inactive) grouped by year, newest first.
     */
    public static function allGroupedByYear(): \Illuminate\Support\Collection
    {
        return static::orderByDesc('year')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('year');
    }
}
