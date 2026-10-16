{{-- Gallery lightbox (design: .lightbox-overlay). Opened by resources/js/modules/lightbox.js. --}}
<div class="lightbox-overlay" data-lightbox role="dialog" aria-modal="true" aria-label="{{ __('web.gallery.preview') }}">
    <button type="button" class="lightbox-close" data-lightbox-close aria-label="{{ __('web.gallery.close') }}">&times;</button>
    <div class="lightbox-box">
        <div class="lightbox-stage">
            <img data-lightbox-img alt="" sizes="(max-width: 960px) 100vw, 960px" decoding="async">
        </div>
        <h3 class="lightbox-title" data-lightbox-title></h3>
        <p class="lightbox-desc" data-lightbox-desc></p>
        <div class="lightbox-nav">
            <button type="button" data-lightbox-prev aria-label="{{ __('web.gallery.prev') }}"><span aria-hidden="true">&larr;</span></button>
            <span class="lightbox-count" data-lightbox-count aria-live="polite"></span>
            <button type="button" data-lightbox-next aria-label="{{ __('web.gallery.next') }}"><span aria-hidden="true">&rarr;</span></button>
        </div>
    </div>
</div>
