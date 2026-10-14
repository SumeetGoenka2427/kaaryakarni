{{-- One campaign: description, live progress, recent contributions, donate form. --}}
<x-layouts.web :title="$campaign->t('title')" :description="\Illuminate\Support\Str::limit($campaign->t('description') ?? __('web.donations.meta_description'), 155)"
               :image="$campaign->image ? url(Storage::disk('public')->url($campaign->image['path'])) : null">
    <x-page-hero :title="$campaign->t('title')" :breadcrumbs="[__('web.nav.donations') => localized_route('donations.index'), $campaign->t('title') => null]">
        @if ($campaign->is_permanent)<x-pill variant="neutral">{{ __('web.donations.permanent_label') }}</x-pill>@endif
    </x-page-hero>

    <section class="section">
        <div class="container">
            <div class="grid grid-2 align-start">
                <div>
                    @if ($campaign->image)
                        <div class="card-media media-rounded content-gap"><x-img :image="$campaign->image" alt="" sizes="(max-width: 600px) 100vw, 50vw" class="cover-img" /></div>
                    @endif
                    @if ($campaign->t('description'))
                        @foreach (preg_split('/\R{2,}/', $campaign->t('description')) as $p)<p>{{ $p }}</p>@endforeach
                    @endif
                    <div class="card content-gap">
                        <div class="card-body">
                            <span class="panel-label">{{ __('web.donations.share_progress') }}</span>
                            @include('web.donations.partials.progress')
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h2 class="info-title">{{ __('web.donations.recent') }}</h2>
                            @forelse ($recent as $d)
                                <div class="flex-between recent-row"><span>{{ $d->publicName() }}</span><strong>{{ inr($d->amount) }}</strong></div>
                            @empty
                                <p class="muted">{{ __('web.donations.no_recent') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div>
                    @if ($campaign->isOpen())
                        <h2>{{ __('web.donations.form_heading') }}</h2>
                        @include('web.donations.partials.form', ['selected' => $campaign])
                    @else
                        <div class="form-alert form-alert-warning" role="status">{{ __('web.donations.closed') }}</div>
                        <a href="{{ localized_route('donations.index') }}" class="btn btn-outline">{{ __('web.donations.all') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.web>
