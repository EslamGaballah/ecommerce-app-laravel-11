@props([
   'name', 'selected' => '', 'label' => false, 'options', 'id' => null
])

@php $id = $id ?? $name; @endphp

<div class="d-flex flex-column text-end w-100">
    @if($label)
        <label for="{{ $id }}" class="form-label fw-bold mb-2">{{ $label }}</label>
    @endif

    {{-- دمجنا الكلاسات وأضفنا ستايل مخصص لضمان محاذاة السهم جهة اليسار في بيئة RTL --}}
    <select 
        name="{{ $name }}"
        id="{{ $id }}"
        {{ $attributes->class([
            'form-control', {{-- أعدنا التنسيق الأساسي للقالب --}}
            'form-select',
            'is-invalid' => $errors->has($name)
        ]) }}
        style="
            background-position: left 0.75rem center; 
            padding-left: 2.25rem; 
            padding-right: 0.75rem;
        "
    >
        @foreach($options as $value => $text)
        <option value="{{ $value }}" @selected($value == $selected)>{{ $text }}</option>
        @endforeach
    </select>

    <x-form.validation-feedback :name="$name" />
</div>