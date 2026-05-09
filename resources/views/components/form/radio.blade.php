@props([
    'name',
    'options' => null,
    'value' => null,
    'label' => false,
    'inline' => false,

])

@if($label)
<label for="">{{ $label }}</label>
@endif

@foreach($options as $optionValue => $text)

<div class="form-check">
    <input class="form-check-input"
            type="radio"
            name="{{ $name }}"
            value="{{ $optionValue }}"

        @checked((string) old($name, $value) === (string) $optionValue)

        {{ $attributes->class([
            'form-check-input',
            'is-invalid' => $errors->has($name)
        ]) }}
    >
    <label class="form-check-label">
        {{ $text }}
    </label>
</div>

@endforeach
