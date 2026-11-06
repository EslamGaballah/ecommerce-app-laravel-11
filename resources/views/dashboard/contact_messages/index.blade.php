@extends('layouts.dashboard')

@section('title', __('app.contact_messages'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.contact_messages') }}</li>
@endsection

@section('content')
<x-alert type="success" />

<table class="table">
    <thead>
        <tr>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.email') }}</th>
            <th>{{ __('app.subject') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.date') }}</th>
            <th class="text-center">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($messages as $message)
            <tr>
                <td>{{ $message->name }}</td>
                <td dir="ltr" class="text-start">{{ $message->email }}</td>
                <td><a href="{{ route('dashboard.messages.show', $message) }}">{{ $message->subject }}</a></td>
                <td>
                    <span class="badge bg-{{ $message->read_at ? 'secondary' : 'primary' }}">{{ $message->read_at ? __('app.message_read') : __('app.message_unread') }}</span>
                </td>
                <td class="text-nowrap">{{ $message->created_at->format('Y-m-d H:i') }}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <a href="{{ route('dashboard.messages.show', $message) }}" class="btn btn-sm btn-outline-success">{{ __('app.show') }}</a>
                        <form action="{{ route('dashboard.messages.destroy', $message) }}" method="POST" class="m-0" onsubmit="return confirm(@js(__('app.are_you_sure')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">{{ __('app.no_contact_messages') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $messages->links('pagination::bootstrap-5') }}
@endsection
