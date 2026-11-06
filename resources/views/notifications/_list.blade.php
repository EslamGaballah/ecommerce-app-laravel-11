<x-alert type="success" />

<div class="notifications-toolbar">
    <div class="notifications-filters">
        <a href="{{ url()->current() }}" @class(['btn btn-sm', 'btn-primary' => ! $onlyUnread, 'btn-outline-primary' => $onlyUnread])>{{ __('app.all') }} ({{ $totalCount }})</a>
        <a href="{{ url()->current() }}?filter=unread" @class(['btn btn-sm', 'btn-primary' => $onlyUnread, 'btn-outline-primary' => ! $onlyUnread])>{{ __('app.message_unread') }} ({{ $unreadCount }})</a>
    </div>

    <div class="notifications-actions">
        @if ($unreadCount)
            <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                @csrf
                <button class="btn btn-sm btn-outline-primary">{{ __('app.mark_all_read') }}</button>
            </form>
        @endif
        @if ($totalCount > $unreadCount)
            <form method="POST" action="{{ route('notifications.destroyRead') }}" class="m-0" onsubmit="return confirm(@js(__('app.are_you_sure')))">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">{{ __('app.delete_read_notifications') }}</button>
            </form>
        @endif
    </div>
</div>

<div class="notifications-list">
    @forelse ($notifications as $notification)
        <div @class(['notification-item', 'is-unread' => ! $notification->read_at])>
            <span class="notification-icon"><i class="lni {{ ($notification->data['kind'] ?? '') === 'order_status' ? 'lni-delivery' : 'lni-cart' }}"></i></span>

            <div class="notification-body">
                <a href="{{ route('notifications.open', $notification->id) }}" class="notification-text">@include('partials.notification-text')</a>
                <small>{{ $notification->created_at->diffForHumans() }}</small>
            </div>

            <div class="notification-buttons">
                @unless ($notification->read_at)
                    <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}" class="m-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-success">{{ __('app.mark_read') }}</button>
                    </form>
                @endunless
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" aria-label="{{ __('app.delete') }}"><i class="lni lni-trash-can"></i></button>
                </form>
            </div>
        </div>
    @empty
        <div class="notifications-empty">
            <i class="lni lni-alarm"></i>
            <p>{{ __('app.no_notifications') }}</p>
        </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $notifications->links('pagination::bootstrap-5') }}
</div>
