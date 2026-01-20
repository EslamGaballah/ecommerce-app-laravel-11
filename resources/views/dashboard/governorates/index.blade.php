@extends('layouts.dashboard')

@section('title', __('app.governorates'))

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{__('app.governorates')}}</li>
@endsection

@section('content')

<div class="mb-5">
    @if(auth()->user()->can('create-governorates'))
        <a href="{{ route('dashboard.governorates.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{__('app.create')}}</a>
        {{-- <a href="{{ route('dashboard.categories.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a>  --}}
     @endif
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- start filter --}}
<form action="{{ url()->current() }}" method="get" class="d-flex justify-content-between mb-4">
    <x-form.input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
    <select name="is_active" class="form-control mx-2">
        <option value="">All</option>
       
        @foreach(['1' => __('app.avilable'),
                '0' => __('app.unAvilable')] as $value => $label)
            <option value="{{ $value }}" @selected(request('is_active') === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    <button class="btn btn-dark mx-2">{{__('app.filter')}}</button>
</form>
{{-- end filter --}}

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>{{__('app.name')}}</th>
            <th>{{__('app.shipping_price')}} </th>
            <th>{{__('app.delivery_days')}} </th>
            <th>{{__('app.shipping_status')}}</th>
            <th> {{__('app.created_at')}}</th>
            <th colspan="3">{{__('app.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($governorates as $governorate)
        <tr>
           
            <td>{{ $governorate->id }}</td>
            <td><a href="#">{{ $governorate->name }}</a></td>

            
            <td>{{ $governorate->shipping_price }}</td>

                
            <td>{{ $governorate->delivery_days }}</td>
            <td>
                <span class="badge bg-{{ $governorate->status_color }}">
                    {{ $governorate->status_label }}
                </span>
            </td>
            <td>{{ $governorate->created_at }}</td>
            <td>
                {{-- @can('categories.update') --}}
                <a href="{{ route('dashboard.governorates.edit', $governorate->id) }}" class="btn btn-sm btn-outline-success">{{__('app.edit')}}</a>
                {{-- @endcan --}}
            </td> 
        </tr>
        @empty
        <tr>
            <td colspan="9">No governorates defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $governorates->appends(request()->query())->links('pagination::bootstrap-5') }}
{{-- {{ $categories->links('pagination::bootstrap-5') }} --}}

{{ $governorates->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}

@endsection