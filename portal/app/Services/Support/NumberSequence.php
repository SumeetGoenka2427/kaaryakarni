<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\DB;

/**
 * Gap-free, concurrency-safe counters (row lock inside a transaction).
 * Used for membership numbers and receipt numbers.
 */
class NumberSequence
{
    public function next(string $key): int
    {
        return DB::transaction(function () use ($key) {
            DB::table('sequences')->insertOrIgnore(['key' => $key, 'value' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('sequences')->where('key', $key)->lockForUpdate()->first();
            $next = $row->value + 1;
            DB::table('sequences')->where('key', $key)->update(['value' => $next, 'updated_at' => now()]);

            return $next;
        });
    }

    /** Indian financial year label for a date: 2026-10-14 → "26-27", 2027-02-01 → "26-27". */
    public static function financialYear(?\DateTimeInterface $date = null): string
    {
        $date ??= now();
        $year = (int) $date->format('Y');
        $start = (int) $date->format('n') >= 4 ? $year : $year - 1;

        return sprintf('%02d-%02d', $start % 100, ($start + 1) % 100);
    }
}
