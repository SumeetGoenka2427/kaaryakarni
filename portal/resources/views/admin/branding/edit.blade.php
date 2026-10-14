<x-layouts.admin title="Branding & Logo">
    <div class="admin-page-head">
        <div>
            <h2>Branding &amp; Logo</h2>
            <p class="muted">Upload the Samaj logo once and it updates everywhere: website header and footer, member area, admin panel, PDF receipts, browser tab icon and social sharing. PNG, JPG or WebP, up to {{ config('portal.uploads.logo_max_kb') / 1024 }} MB. Images are resized and optimised automatically.</p>
        </div>
    </div>

    <div class="branding-grid">
        @foreach ($types as $type => $spec)
            @php $asset = $assets[$type] ?? null; @endphp
            <section class="admin-panel" aria-labelledby="b-{{ $type }}">
                <div class="admin-panel-head">
                    <h3 id="b-{{ $type }}">{{ $spec['label'] }}</h3>
                    @if ($asset)
                        <span class="pill pill-success">Custom · v{{ $asset->version }}</span>
                    @else
                        <span class="pill pill-neutral">Default</span>
                    @endif
                </div>
                <div class="admin-panel-body stack">
                    <div @class(['branding-preview', 'is-dark' => $type === 'logo_footer'])>
                        @if ($asset)
                            <img src="{{ $asset->url() }}" width="{{ $asset->width }}" height="{{ $asset->height }}" alt="{{ $spec['label'] }} preview" loading="lazy">
                        @elseif (str_starts_with($type, 'logo'))
                            <span class="brand-mark" aria-hidden="true">KS</span>
                            <span class="muted">Default “KS” mark</span>
                        @else
                            <span class="muted">Not set</span>
                        @endif
                    </div>
                    <p class="muted branding-help">{{ $spec['help'] }} Output: {{ $spec['width'] }} × {{ $spec['height'] }} px max.</p>

                    <form method="POST" action="{{ route('admin.branding.update', $type) }}" enctype="multipart/form-data" class="form-grid">
                        @csrf
                        <x-field name="file" type="file" :id="'file-'.$type" label="Upload new image" accept="image/png,image/jpeg,image/webp" required />
                        <div class="flex gap-3 flex-wrap">
                            <button type="submit" class="btn btn-primary btn-sm"><span class="spinner" aria-hidden="true"></span> Upload</button>
                        </div>
                    </form>

                    @if ($asset)
                        <form method="POST" action="{{ route('admin.branding.destroy') }}" data-confirm="Remove the {{ $spec['label'] }}? The default will be shown again.">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="type" value="{{ $type }}">
                            <button type="submit" class="btn btn-danger-outline btn-sm">Remove &amp; use default</button>
                        </form>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.admin>
