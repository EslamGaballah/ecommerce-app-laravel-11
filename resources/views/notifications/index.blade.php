@extends('layouts.dashboard')

@section('title', 'Orders')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.notifications')}}</li>
@endsection

@section('content')

<x-alert type="success" />
<x-alert type="info" />


<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>الإشعارات : </h4> 
        <span class="dropdown-header ">
                (  {{ auth()->user()->unreadNotifications->count() }} )
                un read Notifications
        </span>

        @if(auth()->user()->unreadNotifications->count())
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button class="btn btn-sm btn-primary">
                    تعليم الكل كمقروء
                </button>
            </form>
        @endif
    </div>
    <div class="list-group">
        @forelse($notifications as $notification)
            <div class="list-group-item
                {{ $notification->read_at ? '' : 'bg-light' }}">

                <div class="d-flex justify-content-between">
                    <div>
                        <i class="{{ $notification->data['icon'] ?? 'fas fa-bell' }}"></i>
                        {{ $notification->data['message'] ?? $notification->data['body'] ?? 'إشعار جديد' }}
                    </div>

                    <small>
                        {{ $notification->created_at->diffForHumans() }}
                    </small>
                </div>

                <div class="mt-2">
                    <a href="{{ $notification->data['url'] ?? '#' }}"
                       class="btn btn-sm btn-outline-secondary">
                        فتح
                    </a>

                    @if(!$notification->read_at)
                        <form method="POST"
                              action="{{ route('notifications.markAsRead', $notification->id) }}"
                              class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">
                                تعليم كمقروء
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-muted p-4">
                لا يوجد إشعارات
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>

{{ $notifications->appends(request()->query())->links('pagination::bootstrap-5') }} 


{{ $notifications
->withQueryString()->appends(['search' => 1])
->links() }}


@endsection