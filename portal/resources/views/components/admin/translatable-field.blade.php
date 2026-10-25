{{--
    Bilingual admin field: English / हिन्दी tabs for one value stored as
    {name}_en + {name}_hi.

    <x-admin.translatable-field name="title" label="Title" :model="$slide" required />
    <x-admin.translatable-field name="description" label="Description" type="textarea" :model="$campaign" />

    English is required when `required` is set; Hindi is always optional and
    falls back to English on the website. A tab with a validation error is
    opened automatically.

    With JavaScript, these per-field tabs are hidden and replaced by ONE
    English / हिन्दी switch per form (resources/js/modules/lang-switch.js).
--}}
@props([
    'name',
    'label',
    'model' => null,
    'type' => 'text',
    'required' => false,
    'hint' => null,
    'rows' => 4,
    'idPrefix' => '',
])
@php
    $group = 'tr-'.$name.'-'.\Illuminate\Support\Str::random(4);
    $hiHasError = $errors->has($name.'_hi') && ! $errors->has($name.'_en');
@endphp
<div class="translatable-field">
    <div class="tabs tabs-compact" data-tabs="{{ $group }}" role="tablist" aria-label="{{ $label }} language">
        <button type="button" role="tab" class="tab-btn @unless ($hiHasError) is-active @endunless" data-tab="en" aria-selected="{{ $hiHasError ? 'false' : 'true' }}">English</button>
        <button type="button" role="tab" class="tab-btn @if ($hiHasError) is-active @endif" data-tab="hi" aria-selected="{{ $hiHasError ? 'true' : 'false' }}" lang="hi">
            हिन्दी
            @if ($model && blank($model->{$name.'_hi'}) && filled($model->{$name.'_en'}))
                <span class="count" title="Hindi text missing — English will be shown">missing</span>
            @endif
        </button>
    </div>

    <div data-tab-panel="en" data-tabs-group="{{ $group }}" @if ($hiHasError) hidden @endif>
        <x-field :name="$name.'_en'" :id="$idPrefix.'f-'.$name.'_en'" :label="$label.' (English)'" :type="$type" :rows="$rows"
                 :value="$model?->{$name.'_en'}" :required="$required" :hint="$hint" />
    </div>
    <div data-tab-panel="hi" data-tabs-group="{{ $group }}" @unless ($hiHasError) hidden @endunless>
        <x-field :name="$name.'_hi'" :id="$idPrefix.'f-'.$name.'_hi'" :label="$label.' (हिन्दी)'" :type="$type" :rows="$rows"
                 :value="$model?->{$name.'_hi'}" lang="hi"
                 hint="Optional. If left empty, the English text is shown on Hindi pages." />
    </div>
</div>
