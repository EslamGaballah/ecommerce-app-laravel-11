@extends('layouts.dashboard')

@section('title', 'Roles')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ __('Role Details') }}: {{ $role->name }}</h5>
        </div>
        <div class="card-body">
            <p><strong>{{ __('Role Name') }}:</strong> {{ $role->name }}</p>
            <p><strong>{{ __('Created At') }}:</strong> {{ $role->created_at->format('Y-m-d') }}</p>
        </div>
    </div>

    <h5 class="mb-3 text-secondary">{{ __('Assigned Permissions') }}</h5>

    <div class="row">
        @foreach ($groupedPermissions as $groupName => $groupItems)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-light">
                    <div class="card-header bg-secondary text-white text-capitalize">
                        {{ $groupName }}
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($groupItems as $permission)
                                <li class="mb-2">
                                    @if(in_array($permission->id, $rolePermissions))
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i> <strong>{{ ucwords(str_replace('-', ' ', explode('-', $permission->name)[0])) }}</strong>
                                        </span>
                                    @else
                                        <span class="text-muted" style="opacity: 0.5;">
                                            <i class="fas fa-times-circle me-1"></i> {{ ucwords(str_replace('-', ' ', explode('-', $permission->name)[0])) }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <a href="{{ route('dashboard.roles.edit', $role->id) }}" class="btn btn-primary">{{ __('Edit Role') }}</a>
        <a href="{{ route('dashboard.roles.index') }}" class="btn btn-secondary">{{ __('Back to List') }}</a>
    </div>
</div>

@endsection