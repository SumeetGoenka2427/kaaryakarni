{{-- One gallery photo tile; opens the lightbox (JS) and falls back to the full image link without JS. --}}
@php $caption = $img->t('caption'); $label = $caption ?: $album->t('title').' — '.__('web.gallery.photo_n', ['n' => $n]); @endphp
<a href="{{ $img->fullUrl() }}" class="gallery-tile" data-gallery-tile
   data-img="{{ $img->fullUrl() }}" data-srcset="{{ $img->srcset() }}"
   data-title="{{ $caption ?: $album->t('title') }}" data-desc="{{ $caption ? $album->t('title') : '' }}">
    <x-img :image="$img->image" :alt="$label" class="gallery-img" sizes="(max-width: 640px) 50vw, (max-width: 960px) 33vw, 25vw" />
    @if ($caption)<figcaption>{{ $caption }}</figcaption>@endif
</a>
