<x-layouts.admin title="Homepage Banners">
    <div class="admin-page-head">
        <div>
            <h2>Homepage Top Banner Management</h2>
            <p class="muted">The homepage's three-column top banner: the center carousel slides, and the small panels on the left and right. On phones the carousel shows first and the panels stack below it.</p>
        </div>
    </div>

    <section class="admin-panel content-section" aria-labelledby="slides-h">
        <div class="admin-panel-head">
            <h3 id="slides-h">Center Carousel Slides</h3>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">+ Add Slide</a>
        </div>
        @if ($slides->isEmpty())
            <x-empty-state title="No carousel slides configured">
                <p>Add the first slide for the homepage center carousel. Until then a membership message is shown.</p>
            </x-empty-state>
        @else
            <div class="data-table-wrap borderless">
                <table class="data-table">
                    <thead><tr><th scope="col">Slide</th><th scope="col">Order</th><th scope="col">Schedule</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($slides as $s)
                            <tr>
                                <td data-label="Slide">
                                    <div class="flex gap-3 align-center">
                                        <x-img :image="$s->image" alt="" class="slide-thumb" sizes="96px" />
                                        <span><strong>{{ $s->title_en }}</strong>@if ($s->isMissingTranslation())<span class="pill pill-danger">HI missing</span>@endif<br><span class="muted">{{ $s->safeCtaUrl() ?? 'No link' }}</span></span>
                                    </div>
                                </td>
                                <td data-label="Order">{{ $s->sort_order }}</td>
                                <td data-label="Schedule">{{ $s->starts_at?->format('d M Y') ?? 'Now' }} → {{ $s->ends_at?->format('d M Y') ?? 'No end' }}</td>
                                <td data-label="Status">
                                    @php $st = $s->displayStatus(); @endphp
                                    <x-pill :variant="$st === 'Live' ? 'success' : ($st === 'Scheduled' ? 'warning' : 'neutral')">{{ $st }}</x-pill>
                                </td>
                                <td data-label="Actions">
                                    <x-admin.actions>
                                        <a href="{{ route('admin.banners.edit', $s) }}" class="btn btn-outline btn-sm">Edit &amp; Preview</a>
                                        <form method="POST" action="{{ route('admin.banners.toggle', $s) }}" @if ($s->is_active) data-confirm="Deactivate this slide? It will be hidden from the homepage." @endif>
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-outline btn-sm">{{ $s->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.banners.destroy', $s) }}" data-confirm="Delete this slide permanently?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                                        </form>
                                    </x-admin.actions>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="grid grid-2 align-start">
        @foreach (['left' => 'Left Panel', 'right' => 'Right Panel'] as $position => $title)
            @php $panel = $panels[$position]; @endphp
            <form method="POST" action="{{ route('admin.banner-panels.update', $position) }}" enctype="multipart/form-data" class="admin-panel" novalidate>
                @csrf @method('PUT')
                <div class="admin-panel-head"><h3>{{ $title }}</h3></div>
                <div class="admin-panel-body form-grid">
                    <x-field name="mode" :id="'mode-'.$position" type="select" label="Shows" :options="\App\Models\BannerPanel::MODES" :value="$panel->mode" required
                             hint="Use a different campaign mode on each side so the two panels never look the same." />
                    <fieldset class="panel-fieldset">
                        <legend>Live donation settings (campaign modes)</legend>
                        <x-field name="campaign_id" :id="'campaign-'.$position" type="select" label="Campaign" placeholder="Permanent Samaj Donation (default)" :options="$campaigns" :value="$panel->campaign_id"
                                 hint="If the chosen campaign closes, the permanent Samaj Donation is shown instead." />
                        @foreach (\App\Models\BannerPanel::OPTIONS as $key => $label)
                            <label class="checkbox-row"><input type="hidden" name="options[{{ $key }}]" value="0"><input type="checkbox" name="options[{{ $key }}]" value="1" @checked($panel->option($key))> {{ $label }}</label>
                        @endforeach
                    </fieldset>
                    <fieldset class="panel-fieldset">
                        <legend>Advertisement settings (used when “Advertisement” is selected)</legend>
                        <x-admin.image-field name="ad_image" label="Advertisement image" :current="$panel->ad_image" :id-prefix="$position.'-'" />
                        <x-field name="ad_url" :id="'ad-url-'.$position" label="Link" :value="$panel->ad_url" placeholder="/contact or https://…" maxlength="500" />
                        <x-admin.translatable-field name="ad_label" label="Caption / alt text" :model="$panel" :id-prefix="$position.'-'" />
                    </fieldset>
                    <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked($panel->is_active)> Show this panel</label>
                    <div><button type="submit" class="btn btn-primary btn-sm"><span class="spinner" aria-hidden="true"></span> Save {{ strtolower($title) }}</button></div>
                </div>
            </form>
        @endforeach
    </div>
</x-layouts.admin>
