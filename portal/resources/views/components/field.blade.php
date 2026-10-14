{{--
    Labelled form field with hint + server validation error (design: .field).
    <x-field name="email" type="email" :label="__('...')" :value="$member->email" hint="..." required />
    type: text|email|tel|number|date|password|textarea|select|file
    For select pass :options="['value' => 'Label']" and optional :placeholder.
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'options' => [],
    'placeholder' => null,
    'rows' => 3,
    'id' => null,
])
@php
    $id ??= 'f-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $hasError = $errors->has($errorKey);
    $current = old($errorKey, $value);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($hasError ? $id.'-error' : ''));
    $common = $attributes->merge([
        'id' => $id,
        'name' => $name,
        'aria-invalid' => $hasError ? 'true' : null,
        'aria-describedby' => $describedBy ?: null,
        'required' => $required,
    ]);
@endphp
<div @class(['field', 'has-error' => $hasError])>
    <label for="{{ $id }}">{{ $label }} @if ($required)<span class="required" aria-hidden="true">*</span>@endif</label>

    @if ($type === 'textarea')
        <textarea {{ $common->merge(['rows' => $rows, 'placeholder' => $placeholder]) }}>{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select {{ $common }}>
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'file')
        <input type="file" {{ $common }}>
    @else
        <input type="{{ $type }}" value="{{ $type === 'password' ? '' : $current }}" {{ $common->merge(['placeholder' => $placeholder]) }}>
    @endif

    @if ($hint)
        <span class="hint" id="{{ $id }}-hint">{{ $hint }}</span>
    @endif
    @if ($hasError)
        <span class="error-text" id="{{ $id }}-error" role="alert">{{ $errors->first($errorKey) }}</span>
    @endif
</div>
