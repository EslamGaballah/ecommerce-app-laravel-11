@foreach (App\Models\Setting::SOCIAL_NETWORKS as $network => $icon)
    @if (Setting::get($network))
        <li>
            <a href="{{ Setting::get($network) }}" target="_blank" rel="noopener" aria-label="{{ __('app.'.$network) }}"><i class="lni lni-{{ $icon }}"></i></a>
        </li>
    @endif
@endforeach
@if (Setting::get('whatsapp'))
    <li>
        <a href="https://wa.me/{{ Setting::get('whatsapp') }}" target="_blank" rel="noopener" aria-label="{{ __('app.whatsapp') }}"><i class="lni lni-whatsapp"></i></a>
    </li>
@endif
