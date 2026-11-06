@extends('layouts.dashboard')

@section('title', $message->subject)

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('dashboard.messages.index') }}">{{ __('app.contact_messages') }}</a></li>
    <li class="breadcrumb-item active">{{ $message->subject }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">{{ $message->subject }}</div>
    <div class="card-body">
        <p><strong>{{ __('app.name') }}:</strong> {{ $message->name }}</p>
        <p><strong>{{ __('app.email') }}:</strong> <a href="mailto:{{ $message->email }}" dir="ltr">{{ $message->email }}</a></p>
        @if ($message->phone)
            <p><strong>{{ __('app.phone') }}:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $message->phone) }}" dir="ltr">{{ $message->phone }}</a></p>
        @endif
        <p><strong>{{ __('app.date') }}:</strong> {{ $message->created_at->format('Y-m-d H:i') }}</p>
        <hr>
        <p class="mb-0">{!! nl2br(e($message->message)) !!}</p>
    </div>
</div>

<div class="d-flex gap-2">
    <a href="{{ route('dashboard.messages.index') }}" class="btn btn-outline-dark">{{ __('app.back') }}</a>
    <form action="{{ route('dashboard.messages.destroy', $message) }}" method="POST" class="m-0" onsubmit="return confirm(@js(__('app.are_you_sure')))">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">{{ __('app.delete') }}</button>
    </form>
</div>
@endsection
