@php $editing = $block->exists; $f = $spec['fields']; @endphp
<x-layouts.admin :title="($editing ? 'Edit' : 'Add').' — '.$spec['label']">
    <div class="admin-page-head">
        <div>
            <h2>{{ $spec['page_label'] }} › {{ $spec['label'] }}</h2>
            @if (! empty($spec['help']))<p class="muted">{{ $spec['help'] }}</p>@endif
        </div>
        <a href="{{ route('admin.content.index', ['page' => $spec['page']]) }}" class="btn btn-outline">&larr; Back</a>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate class="admin-panel settings-panel"
          action="{{ $editing ? route('admin.content.update', $block) : route('admin.content.store', [$spec['page'], $spec['section']]) }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            @if (in_array('label', $f, true))
                <x-admin.translatable-field name="label" :label="$spec['section'] === 'about' ? 'Button text' : 'Label'" :model="$block" />
            @endif
            @if (in_array('title', $f, true))
                <x-admin.translatable-field name="title" :label="$spec['section'] === 'faqs' ? 'Question' : 'Title'" :model="$block" required />
            @endif
            @if (in_array('body', $f, true))
                <x-admin.translatable-field name="body" :label="$spec['section'] === 'faqs' ? 'Answer' : 'Text'" type="textarea" :rows="6" :model="$block" :required="! in_array('title', $f, true)" />
            @endif
            @if (in_array('link_url', $f, true))
                <x-field name="link_url" :label="$spec['section'] === 'about' ? 'Button link' : 'Link'" :value="$block->link_url" placeholder="/register" hint="A site path like /register or a full https:// link." maxlength="500" />
            @endif
            @if (in_array('image', $f, true))
                <x-admin.image-field :current="$block->image" />
            @endif
            <div class="form-row">
                <x-field name="sort_order" type="number" label="Display order" :value="$block->sort_order" min="0" max="999" hint="Lower numbers appear first." />
                <label class="checkbox-row align-self-end">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $block->is_active))> Visible on the website
                </label>
            </div>
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save</button></div>
        </div>
    </form>
</x-layouts.admin>
