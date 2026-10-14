{{-- Admin image upload with current preview and "remove" option. --}}
@props(['name' => 'image', 'label' => 'Image', 'current' => null, 'hint' => null, 'removable' => true, 'idPrefix' => ''])
<div class="field image-field">
    <span class="field-label">{{ $label }}</span>
    @if ($current)
        <div class="image-field-preview">
            <x-img :image="$current" alt="Current {{ strtolower($label) }}" sizes="240px" />
        </div>
        @if ($removable)
            <label class="checkbox-row"><input type="checkbox" name="remove_{{ $name }}" value="1"> Remove current image</label>
        @endif
    @endif
    <x-field :name="$name" :id="$idPrefix.'f-'.$name" type="file" :label="$current ? 'Replace image' : 'Upload image'" accept="image/jpeg,image/png,image/webp"
             :hint="$hint ?? 'JPG, PNG or WebP, up to '.(config('portal.uploads.image_max_kb') / 1024).' MB. Resized and optimised automatically.'" />
</div>
