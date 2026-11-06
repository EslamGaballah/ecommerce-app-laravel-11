<div class="custom-control custom-switch mb-3">
    <input type="checkbox" class="custom-control-input" id="{{ $name }}" name="{{ $name }}" value="1" @checked(old($name, $settings[$name]))>
    <label class="custom-control-label" for="{{ $name }}">{{ $label }}</label>
    @isset($help)
        <small class="text-muted d-block">{{ $help }}</small>
    @endisset
</div>
