@extends('layouts.dashboard')

@section('title', __('app.roles'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.roles') }}</li>
@endsection

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ __('app.role_details') }}: {{ $role->name }}</h5>
        </div>
        <div class="card-body">
            <p><strong>{{ __('app.role_name') }}:</strong> {{ $role->name }}</p>
            <p><strong>{{ __('app.created_at') }}:</strong> {{ $role->created_at->format('Y-m-d') }}</p>
        </div>
    </div>

    <h5 class="mb-3 text-secondary">{{ __('app.assigned_permissions') }}</h5>

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
        <a href="{{ route('dashboard.roles.edit', $role->id) }}" class="btn btn-primary">{{ __('app.edit_role') }}</a>
        <a href="{{ route('dashboard.roles.index') }}" class="btn btn-secondary">{{ __('app.back') }}</a>
    </div>
</div>

@endsection