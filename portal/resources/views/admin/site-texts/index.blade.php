<x-layouts.admin title="Site Texts">
    <div class="admin-page-head">
        <div>
            <h2>Website Texts</h2>
            <p class="muted">Edit any heading, label or message shown on the website, in English and हिन्दी. Clear a box to restore its original text. Changes appear on the site immediately.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.site-texts.index') }}" class="filters-bar">
        <div class="field">
            <label for="st-section">Section</label>
            <select id="st-section" name="section" data-autosubmit>
                @foreach ($sections as $id => $label)
                    <option value="{{ $id }}" @selected($id === $current)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field search-field">
            <label for="st-q">Search all texts</label>
            <input type="search" id="st-q" name="q" value="{{ $search }}" placeholder="e.g. Donate, membership, सदस्य">
        </div>
        <label class="checkbox-row filter-check">
            <input type="checkbox" name="missing" value="1" @checked($onlyMissing)> Only texts without Hindi
        </label>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if ($search !== '' || $onlyMissing)
                <a href="{{ route('admin.site-texts.index', ['section' => $current]) }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </div>
    </form>

    @if ($rows->isEmpty())
        <x-empty-state title="No texts match your filter" icon="search">
            <p>Try a different search word or section.</p>
        </x-empty-state>
    @else
        <form method="POST" action="{{ route('admin.site-texts.update') }}">
            @csrf
            @method('PUT')
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h3>{{ $search !== '' || $onlyMissing ? 'Search results' : $sections[$current] }} <span class="muted">({{ $rows->count() }})</span></h3>
                    <button type="submit" class="btn btn-primary btn-sm"><span class="spinner" aria-hidden="true"></span> Save Changes</button>
                </div>
                <div class="data-table-wrap site-texts-table">
                    <table class="data-table">
                        <thead>
                            <tr><th scope="col">Text</th><th scope="col">English</th><th scope="col">हिन्दी</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $row)
                                @php $rows_en = max(1, (int) ceil(mb_strlen($row['en']) / 60)); @endphp
                                <tr>
                                    <td data-label="Text">
                                        <code class="text-key">{{ $row['key'] }}</code>
                                        @if ($row['override_en'] || $row['override_hi'])
                                            <span class="pill pill-warning">Edited</span>
                                        @endif
                                        @if (blank($row['hi']))
                                            <span class="pill pill-danger">HI missing</span>
                                        @endif
                                    </td>
                                    <td data-label="English">
                                        <label class="visually-hidden" for="t{{ $i }}en">{{ $row['key'] }} English</label>
                                        <textarea id="t{{ $i }}en" name="texts[{{ $row['id'] }}][en]" rows="{{ min(6, $rows_en) }}" placeholder="{{ $row['default_en'] }}" maxlength="2000">{{ $row['override_en'] ?? $row['default_en'] }}</textarea>
                                    </td>
                                    <td data-label="हिन्दी">
                                        <label class="visually-hidden" for="t{{ $i }}hi">{{ $row['key'] }} Hindi</label>
                                        <textarea id="t{{ $i }}hi" name="texts[{{ $row['id'] }}][hi]" lang="hi" rows="{{ min(6, $rows_en) }}" placeholder="{{ $row['default_hi'] }}" maxlength="2000">{{ $row['override_hi'] ?? $row['default_hi'] }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="settings-actions">
                <button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save Changes</button>
            </div>
        </form>
    @endif
</x-layouts.admin>
