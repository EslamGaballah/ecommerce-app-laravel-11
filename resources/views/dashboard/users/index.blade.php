@extends('layouts.dashboard')

@section('title', 'users')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.users')}}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.users.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>{{__('app.name')}}</th>
            <th>{{__('app.email')}}</th>
            <th>{{__('app.roles')}}</th>
            <th>{{__('app.created_at')}}</th>
            <th colspan="2">{{__('app.action')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td><a href="{{ route('dashboard.users.show', $user->id) }}">{{ $user->name }}</a></td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->roles->first()?->name }}</td>
            <td>{{ $user->created_at }}</td>
            <td>
                @can('users.update')
                <a href="{{ route('dashboard.users.edit', $user->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                @endcan
            </td>
            <td>
                @can('users.delete')
                <form action="{{ route('dashboard.users.destroy', $user->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{__('app.delete')}}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6">No users defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $users->withQueryString()->links() }}

@endsection