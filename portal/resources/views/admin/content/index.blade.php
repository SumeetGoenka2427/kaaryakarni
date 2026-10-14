<x-layouts.admin title="Page Content">
    <div class="admin-page-head">
        <div>
            <h2>Page Content</h2>
            <p class="muted">Paragraphs, cards, images and lists on the public pages, in English and हिन्दी. Page headings and button labels are under <a href="{{ route('admin.site-texts.index') }}">Site Texts</a>; office address, phone and membership fee are under <a href="{{ route('admin.settings.edit') }}">Settings</a>.</p>
        </div>
    </div>

    <nav class="tabs" aria-label="Pages">
        @foreach ($pages as $key => $p)
            <a href="{{ route('admin.content.index', ['page' => $key]) }}" @class(['tab-btn', 'is-active' => $key === $page]) @if ($key === $page) aria-current="page" @endif>{{ $p['label'] }}</a>
        @endforeach
    </nav>

    @foreach ($pages[$page]['sections'] as $sectionKey => $spec)
        @php $items = $blocks->get($sectionKey, collect()); @endphp
        <section class="admin-panel content-section" aria-labelledby="sec-{{ $sectionKey }}">
            <div class="admin-panel-head">
                <div>
                    <h3 id="sec-{{ $sectionKey }}">{{ $spec['label'] }}</h3>
                    @if (! empty($spec['help']))<p class="muted panel-help">{{ $spec['help'] }}</p>@endif
                </div>
                @if (! $spec['single'] || $items->isEmpty())
                    <a href="{{ route('admin.content.create', [$page, $sectionKey]) }}" class="btn btn-primary btn-sm">+ Add</a>
                @endif
            </div>
            @if ($items->isEmpty())
                <div class="admin-panel-body"><p class="muted">Nothing here yet — this part of the page is hidden until content is added.</p></div>
            @else
                <div class="data-table-wrap borderless">
                    <table class="data-table">
                        <thead><tr><th scope="col">Content</th><th scope="col">Order</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($items as $block)
                                <tr>
                                    <td data-label="Content">
                                        <strong>{{ $block->title_en ?: \Illuminate\Support\Str::limit($block->body_en, 80) }}</strong>
                                        @if ($block->isMissingTranslation())<span class="pill pill-danger">HI missing</span>@endif
                                        @if ($block->title_en && $block->body_en)<br><span class="muted">{{ \Illuminate\Support\Str::limit($block->body_en, 110) }}</span>@endif
                                    </td>
                                    <td data-label="Order">{{ $block->sort_order }}</td>
                                    <td data-label="Status"><x-pill :variant="$block->is_active ? 'success' : 'neutral'">{{ $block->is_active ? 'Visible' : 'Hidden' }}</x-pill></td>
                                    <td data-label="Actions">
                                        <div class="row-actions">
                                            <a href="{{ route('admin.content.edit', $block) }}" class="btn btn-outline btn-sm">Edit</a>
                                            @unless ($spec['single'])
                                                <form method="POST" action="{{ route('admin.content.destroy', $block) }}" data-confirm="Delete this item? This cannot be undone.">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endforeach
</x-layouts.admin>
