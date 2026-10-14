@php $editing = $slide->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Slide' : 'Add Slide'">
    <div class="admin-page-head">
        <div>
            <h2>{{ $editing ? 'Edit Carousel Slide' : 'Add Carousel Slide' }}</h2>
            <p class="muted">Wide landscape photos work best (shown at about 2 : 1). Text is placed over the bottom-left of the image.</p>
        </div>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline">&larr; All Banners</a>
    </div>

    @if ($editing && $slide->image)
        <section class="admin-panel settings-panel" aria-labelledby="preview-h">
            <div class="admin-panel-head"><h3 id="preview-h">Preview</h3><span class="muted">Status: {{ $slide->displayStatus() }}</span></div>
            <div class="admin-panel-body grid grid-2">
                @foreach (['en' => 'English', 'hi' => 'हिन्दी'] as $loc => $label)
                    <div>
                        <p class="muted preview-label">{{ $label }}</p>
                        <div class="banner-carousel slide-preview" lang="{{ $loc }}">
                            <div class="carousel-track">
                                <div class="carousel-slide is-active">
                                    <x-img :image="$slide->image" class="carousel-slide-img" alt="" sizes="420px" />
                                    <span class="carousel-slide-scrim" aria-hidden="true"></span>
                                    <span class="carousel-slide-content">
                                        <span class="carousel-slide-title">{{ $slide->t('title', $loc) }}</span>
                                        @if ($slide->t('subtitle', $loc))<span class="carousel-slide-subtitle">{{ $slide->t('subtitle', $loc) }}</span>@endif
                                        @if ($slide->safeCtaUrl() && $slide->t('cta_label', $loc))<span class="carousel-slide-cta">{{ $slide->t('cta_label', $loc) }}</span>@endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <form method="POST" enctype="multipart/form-data" novalidate class="admin-panel settings-panel"
          action="{{ $editing ? route('admin.banners.update', $slide) : route('admin.banners.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <x-admin.image-field :current="$slide->image" :removable="false" :hint="'Required. JPG, PNG or WebP, at least 1280 px wide recommended, up to '.(config('portal.uploads.image_max_kb') / 1024).' MB.'" />
            <x-admin.translatable-field name="title" label="Title" :model="$slide" required />
            <x-admin.translatable-field name="subtitle" label="Subtitle / description" type="textarea" :rows="2" :model="$slide" />
            <div class="form-row">
                <x-admin.translatable-field name="cta_label" label="Button text" :model="$slide" />
                <x-field name="cta_url" label="Button / slide link" :value="$slide->cta_url" placeholder="/membership" maxlength="500" hint="Site path like /membership or a full https:// link." />
            </div>
            <div class="form-row-3">
                <x-field name="sort_order" type="number" label="Display order" :value="$slide->sort_order" min="0" max="999" />
                <x-field name="starts_at" type="date" label="Show from (optional)" :value="$slide->starts_at?->toDateString()" />
                <x-field name="ends_at" type="date" label="Hide after (optional)" :value="$slide->ends_at?->toDateString()" />
            </div>
            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide->is_active))> Active (shown in the homepage carousel)</label>
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save Slide</button></div>
        </div>
    </form>
</x-layouts.admin>
