{{-- One gallery album: all photos, lightbox preview. --}}
<x-layouts.web :title="$album->t('title')" :description="$album->t('description') ?: __('web.gallery.meta_description')"
               :image="($c = $images->first()) ? url($c->fullUrl()) : null">
    <x-page-hero :title="$album->t('title')" :subtitle="$album->t('description')"
                 :breadcrumbs="[__('web.gallery.title') => localized_route('gallery.index'), $album->t('title') => null]">
        <p class="gallery-hero-meta">@if ($album->event_date){{ $album->event_date->translatedFormat('d F Y') }} · @endif{{ trans_choice('web.gallery.photos', $album->images_count, ['count' => $album->images_count]) }}</p>
    </x-page-hero>

    <section class="section">
        <div class="container">
            <div class="gallery-grid" data-gallery-group>
                @foreach ($images as $img)
                    @include('web.gallery.tile', ['img' => $img, 'album' => $album, 'n' => $images->firstItem() + $loop->index])
                @endforeach
            </div>
            <div class="pagination-wrap">{{ $images->links() }}</div>
            <p class="text-center gallery-share-note"><a href="{{ localized_route('gallery.index') }}" class="btn btn-outline">&larr; {{ __('web.gallery.back') }}</a></p>
        </div>
    </section>

    @include('web.gallery.lightbox')
</x-layouts.web>
