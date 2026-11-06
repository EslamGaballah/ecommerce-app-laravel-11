@if (Setting::get('maintenance_mode'))
    <div class="maintenance-banner" role="status">
        {{ __('app.maintenance_banner') }}
        @can('manage-settings')
            <a href="{{ route('dashboard.settings.edit') }}#maintenance">{{ __('app.settings') }}</a>
        @endcan
    </div>
@endif
