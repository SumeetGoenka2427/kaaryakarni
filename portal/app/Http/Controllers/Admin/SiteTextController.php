<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteText;
use App\Services\Audit\AuditLogger;
use App\Translation\DatabaseOverrideLoader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin → Site Texts: edit every website label/heading in English and Hindi.
 *
 * Defaults stay in lang/{en,hi}/*.php; only values that differ from the
 * default are stored in `site_texts`. Clearing a box restores the default.
 */
class SiteTextController extends Controller
{
    public function index(Request $request): View
    {
        $sections = $this->sections();
        $current = (string) $request->query('section', array_key_first($sections));
        abort_unless(isset($sections[$current]), 404);

        $search = trim((string) $request->query('q', ''));
        $onlyMissing = $request->boolean('missing');

        // Searching / "Hindi missing" look across all sections; otherwise show the chosen one.
        $rows = collect($search !== '' || $onlyMissing ? array_keys($sections) : [$current])
            ->flatMap(fn ($section) => $this->rows($section))
            ->filter(function ($row) use ($search, $onlyMissing) {
                if ($onlyMissing && filled($row['hi'])) {
                    return false;
                }

                return $search === '' || Str::contains(Str::lower($row['key'].' '.$row['en'].' '.$row['hi']), Str::lower($search));
            })
            ->values();

        return view('admin.site-texts.index', compact('sections', 'current', 'rows', 'search', 'onlyMissing'));
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'texts' => ['required', 'array', 'max:400'],
            'texts.*.en' => ['nullable', 'string', 'max:2000'],
            'texts.*.hi' => ['nullable', 'string', 'max:2000'],
        ]);

        $changed = [];

        DB::transaction(function () use ($data, &$changed) {
            foreach ($data['texts'] as $id => $values) {
                // Ids arrive as "group|dot.key" and must exist in the English defaults.
                [$group, $key] = array_pad(explode('|', (string) $id, 2), 2, null);
                if (! in_array($group, DatabaseOverrideLoader::EDITABLE_GROUPS, true) || ! $key) {
                    continue;
                }
                $defaults = $this->defaults($group);
                if (! is_string($defaults['en'][$key] ?? null)) {
                    continue;
                }

                // Store only what differs from the file default.
                $en = $this->clean($values['en'] ?? null, $defaults['en'][$key]);
                $hi = $this->clean($values['hi'] ?? null, $defaults['hi'][$key] ?? null);

                $existing = SiteText::where('group', $group)->where('key', $key)->first();
                $before = [$existing?->value_en, $existing?->value_hi];

                if ($en === null && $hi === null) {
                    $existing?->delete();
                } else {
                    SiteText::updateOrCreate(['group' => $group, 'key' => $key], ['value_en' => $en, 'value_hi' => $hi]);
                }

                if ($before !== [$en, $hi]) {
                    $changed[] = "{$group}.{$key}";
                }
            }
        });

        foreach (DatabaseOverrideLoader::EDITABLE_GROUPS as $group) {
            DatabaseOverrideLoader::flush($group);
        }

        if ($changed) {
            $audit->record('site_texts.updated', null, 'Updated: '.Str::limit(implode(', ', $changed), 240), [], ['keys' => $changed]);
        }

        return back()->with('success', $changed ? count($changed).' text(s) updated on the website.' : 'No changes to save.');
    }

    /** section id ("web.home") => label ("Home") */
    private function sections(): array
    {
        $sections = [];
        foreach (DatabaseOverrideLoader::EDITABLE_GROUPS as $group) {
            foreach ($this->defaults($group)['tree'] as $top => $value) {
                $label = Str::headline($top);
                $sections["{$group}.{$top}"] = $group === 'enums' ? "Status labels: {$label}" : $label;
            }
        }

        return $sections;
    }

    private function rows(string $section): array
    {
        [$group, $top] = explode('.', $section, 2);
        $defaults = $this->defaults($group);
        $overrides = SiteText::where('group', $group)
            ->where(fn ($q) => $q->where('key', $top)->orWhere('key', 'like', $top.'.%'))
            ->get()
            ->keyBy('key');

        $rows = [];
        foreach ($defaults['en'] as $key => $en) {
            if (! is_string($en) || ($key !== $top && ! str_starts_with($key, $top.'.'))) {
                continue;
            }
            $override = $overrides[$key] ?? null;
            $rows[] = [
                'id' => "{$group}|{$key}",
                'key' => "{$group}.{$key}",
                'default_en' => $en,
                'default_hi' => $defaults['hi'][$key] ?? '',
                'override_en' => $override?->value_en,
                'override_hi' => $override?->value_hi,
                'en' => $override?->value_en ?: $en,
                'hi' => $override?->value_hi ?: ($defaults['hi'][$key] ?? ''),
            ];
        }

        return $rows;
    }

    /** File defaults (without DB overrides): nested tree + flattened dot keys. */
    private function defaults(string $group): array
    {
        static $cache = [];

        if (! isset($cache[$group])) {
            $en = require lang_path("en/{$group}.php");
            $hi = require lang_path("hi/{$group}.php");
            $cache[$group] = ['tree' => $en, 'en' => Arr::dot($en), 'hi' => Arr::dot($hi)];
        }

        return $cache[$group];
    }

    private function clean(?string $value, ?string $default): ?string
    {
        $value = trim((string) $value);

        return $value === '' || $value === $default ? null : $value;
    }
}
