{{-- Gallery (web/gallery.html): each active album with its title, description and photos; lightbox preview. --}}
<x-layouts.web :title="__('web.gallery.title')" :description="__('web.gallery.meta_description')"
               :image="($c = $albums->first()?->images->first()) ? url($c->fullUrl()) : null">
    <x-page-hero :title="__('web.gallery.title')" :subtitle="__('web.gallery.subtitle')" :breadcrumbs="[__('web.gallery.title') => null]" />

    <section class="section">
        <div class="container">
            @forelse ($albums as $album)
                <section class="gallery-album" data-gallery-group aria-labelledby="album-{{ $album->id }}">
                    <div class="gallery-album-head">
                        <div>
                            <h2 id="album-{{ $album->id }}">{{ $album->t('title') }}</h2>
                            <p class="muted gallery-album-meta">
                                @if ($album->event_date){{ $album->event_date->translatedFormat('d F Y') }} · @endif{{ trans_choice('web.gallery.photos', $album->images_count, ['count' => $album->images_count]) }}
                            </p>
                            @if ($album->t('description'))<p class="muted gallery-album-desc">{{ $album->t('description') }}</p>@endif
                        </div>
                        @if ($album->images_count > $album->images->count())
                            <a href="{{ localized_route('gallery.show', ['album' => $album->slug]) }}" class="btn btn-outline btn-sm">{{ __('web.gallery.view_all', ['count' => $album->images_count]) }}</a>
                        @endif
                    </div>
                    <div class="gallery-grid">
                        @foreach ($album->images as $img)
                            @include('web.gallery.tile', ['img' => $img, 'album' => $album, 'n' => $loop->iteration])
                        @endforeach
                    </div>
                </section>
            @empty
                <x-empty-state icon="image" :title="__('web.gallery.empty')">
                    <p>{{ __('web.gallery.empty_text') }}</p>
                </x-empty-state>
            @endforelse

            <div class="pagination-wrap">{{ $albums->links() }}</div>
            <p class="muted text-center gallery-share-note"><a href="{{ localized_route('contact') }}">{{ __('web.gallery.share') }}</a></p>
        </div>
    </section>

    @include('web.gallery.lightbox')
</x-layouts.web>
