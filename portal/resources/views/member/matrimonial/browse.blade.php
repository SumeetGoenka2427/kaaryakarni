{{-- Member → Browse Matrimonial (web/member/matrimonial-browse.html). Approved profiles only, eligible members only. --}}
@php use App\Enums\Gender; $f = fn ($k) => $filters[$k] ?? null; @endphp
<x-layouts.member :title="__('web.matrimonial.browse_title')">
    <section class="section section-tight">
        <div class="container">
            <div class="page-title-row">
                <h1>{{ __('web.matrimonial.browse_title') }}</h1>
                <a href="{{ localized_route('member.matrimonial.index') }}" class="btn btn-outline btn-sm">&larr; {{ __('web.matrimonial.back_mine') }}</a>
            </div>
            <div class="form-alert form-alert-info" role="note">{{ __('web.matrimonial.browse_intro') }}</div>

            <form method="GET" action="{{ localized_route('member.matrimonial.browse') }}" class="filters-bar" role="search">
                <div class="field">
                    <label for="mf-gender">{{ __('web.matrimonial.gender') }}</label>
                    <select id="mf-gender" name="gender" data-autosubmit>
                        <option value="">{{ __('web.matrimonial.any') }}</option>
                        @foreach ([Gender::Male, Gender::Female] as $g)<option value="{{ $g->value }}" @selected($f('gender') === $g->value)>{{ $g->label() }}</option>@endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="mf-age-min">{{ __('web.matrimonial.min_age') }}</label>
                    <input type="number" id="mf-age-min" name="min_age" min="18" max="80" placeholder="18" value="{{ $f('min_age') }}" inputmode="numeric">
                </div>
                <div class="field">
                    <label for="mf-age-max">{{ __('web.matrimonial.max_age') }}</label>
                    <input type="number" id="mf-age-max" name="max_age" min="18" max="80" placeholder="60" value="{{ $f('max_age') }}" inputmode="numeric">
                </div>
                <div class="field">
                    <label for="mf-area">{{ __('web.matrimonial.area') }}</label>
                    <select id="mf-area" name="area" data-autosubmit>
                        <option value="">{{ __('web.matrimonial.any_area') }}</option>
                        @foreach ($areas as $id => $name)<option value="{{ $id }}" @selected((int) $f('area') === $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="field search-field">
                    <label for="mf-q">{{ __('web.matrimonial.search') }}</label>
                    <input type="search" id="mf-q" name="q" value="{{ $f('q') }}" placeholder="{{ __('web.matrimonial.search_placeholder') }}" maxlength="80">
                </div>
                <div class="flex gap-3 flex-wrap">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('web.common.filter') }}</button>
                    @if (array_filter($filters))<a href="{{ localized_route('member.matrimonial.browse') }}" class="btn btn-outline btn-sm">{{ __('web.business.clear') }}</a>@endif
                </div>
            </form>

            <p class="muted results-count" role="status">{{ __('web.matrimonial.results', ['count' => $profiles->total()]) }}</p>

            @if ($profiles->isEmpty())
                <x-empty-state icon="search" :title="__('web.matrimonial.no_results')">
                    <p>{{ __('web.matrimonial.no_results_text') }}</p>
                </x-empty-state>
            @else
                <div class="match-grid">
                    @foreach ($profiles as $m)
                        <a href="{{ localized_route('member.matrimonial.show', ['profile' => $m->ulid]) }}" class="card match-card">
                            <div class="match-card-media">
                                @if ($photo = $m->photos->first())
                                    <img src="{{ route('matrimonial.photo', [$photo->id, 320]) }}" alt="{{ $m->name }}" width="320" height="400" loading="lazy" decoding="async">
                                @else
                                    <span class="thumb-fallback" aria-hidden="true">{{ initials($m->name) }}</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="flex-between gap-3">
                                    <h2 class="mat-card-name">{{ $m->name }}</h2>
                                    <x-pill variant="success">{{ __('web.matrimonial.approved') }}</x-pill>
                                </div>
                                <p class="muted mat-card-meta">{{ __('web.matrimonial.age_years', ['age' => $m->age()]) }} · {{ $m->gender->label() }}@if ($m->heightLabel()) · {{ $m->heightLabel() }}@endif</p>
                                <p class="muted mat-card-meta">{{ $m->occupation ?: '—' }} · {{ $m->education ?: '—' }}</p>
                                <p class="muted mat-card-meta">{{ $m->area ? $m->area->t('name').', ' : '' }}{{ __('web.common.jaipur') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $profiles->links() }}</div>
            @endif
            <p class="muted privacy-note">{{ __('web.matrimonial.private_note') }}</p>
        </div>
    </section>
</x-layouts.member>
