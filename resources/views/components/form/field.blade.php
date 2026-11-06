@props(['name', 'label', 'type' => 'text', 'value' => null, 'bag' => 'default', 'id' => null])

@php
    $fieldId = $id ?? $name;
    $fieldErrors = $errors->getBag($bag)->get($name);
@endphp

<div class="form-group mb-3">
    <label for="{{ $fieldId }}">{{ $label }}</label>
    <input id="{{ $fieldId }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
        {{ $attributes->class(['form-control', 'is-invalid' => $fieldErrors]) }}>
    @foreach ($fieldErrors as $message)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @endforeach
</div>
