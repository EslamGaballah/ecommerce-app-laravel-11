@extends('layouts.dashboard')

@section('title', __('app.products') )

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">{{ __('app.products') }}</li>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ route('dashboard.products.create') }}" class="btn btn-sm btn-outline-primary mr-2">{{ __('app.create') }}</a>
     <a href="{{ route('dashboard.products.trash') }}" class="btn btn-sm btn-outline-dark">{{ __('app.trash') }}</a>
</div>

<x-alert type="success" />
<x-alert type="info" />

{{-- filter form --}}
<form action="{{ request()->url() }}" method="get" 
    class="d-flex flex-wrap flex-md-nowrap align-items-stretch gap-2 mb-4 overflow-auto pb-2 flex-row-reverse">
    
    {{-- 🔘 submit --}}
    <button type="submit" class="btn btn-dark mb-0 text-nowrap d-flex align-items-center justify-content-center px-4">
        {{ __('app.filter') }}
    </button>

    {{-- 🔽 sort --}}
    <select name="sort" class="form-control mb-0" style="min-width: 140px; height: 38px; padding-left: 20px; background-position: left 0.5rem center;">
        <option value="">{{ __('app.sort_by') }}</option>
        <option value="low_price"  @selected(request('sort') == 'low_price')>{{ __('app.low_price') }}</option>
        <option value="high_price" @selected(request('sort') == 'high_price')>{{ __('app.high_price') }}</option>
        <option value="newest"     @selected(request('sort') == 'newest')>{{ __('app.newest') }}</option>
        <option value="oldest"     @selected(request('sort') == 'oldest')>{{ __('app.oldest') }}</option>
    </select>

    {{-- ⚡ status --}}
    <select name="status" class="form-control mb-0" style="min-width: 120px; height: 38px; padding-left: 20px; background-position: left 0.5rem center;">
        <option value="">{{ __('app.all') }}</option>
        @foreach(\App\Enums\ProductStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                {{ $status->label() }}
            </option>
        @endforeach
    </select>

    {{-- 🏷 brand --}}
    <select name="brand" class="form-control mb-0" style="min-width: 140px; height: 38px; padding-left: 20px; background-position: left 0.5rem center;">
        <option value="">{{ __('app.brands') }}</option>
        @foreach($brands as $brand)
            <option value="{{ $brand->id }}" @selected(request('brand') == $brand->id)>
                {{ $brand->name }}
            </option>
        @endforeach
    </select>

    {{-- 📂 category --}}
    <select name="category" class="form-control mb-0" style="min-width: 140px; height: 38px; padding-left: 20px; background-position: left 0.5rem center;">
        <option value="">{{ __('app.categories') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    {{-- 🔍 search --}}
    <div style="min-width: 200px; flex-grow: 1;">
        <x-form.input 
            name="search" 
            placeholder="Search..." 
            class="form-control mb-0 h-100"
            :value="request('search')" 
        />
    </div>

</form>


<table class="table align-middle"> 
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.stock') }}</th>
            <th>{{ __('app.price') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.created_at') }}</th>
            <th class="text-center">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td><a href="{{ route('dashboard.products.show', $product->id) }}">{{ $product->name }}</a></td> 
            <td>{{ $product->category->name }}</td>
            <td>
                @if($product->stock)
                    {{ $product->stock}}
                @else
                    {{ $product->total_quantity }}
                @endif
            </td>
            <td>
                @if ($product->price && $product->compare_price)
                     <span class="fw-bold">{{ $product->price }}</span>
                     <span class="text-muted text-decoration-line-through">{{ $product->compare_price }}</span>
                @elseif($product->primaryVariation && $product->primaryVariation->compare_price > $product->price)
                    <span class="fw-bold">{{ $product->primaryVariation->price }}</span>
                    <span class="text-muted text-decoration-line-through">{{ $product->primaryVariation->compare_price }}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-{{ $product->status->color() }} badge-{{ $product->id }}">
                    {{ $product->status->label() }}
                </span>
            </td>
            <td>{{ $product->created_at }}</td>
            
            <td class="text-center" style="width: 220px;">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    
                    <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-sm btn-outline-success text-nowrap m-0">
                        {{ __('app.show') }}
                    </a>
                    
                    <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-sm btn-outline-success text-nowrap m-0">
                        {{ __('app.edit') }}
                    </a>
                    
                    <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post" class="m-0 d-inline">
                        @csrf
                        <input type="hidden" name="_method" value="delete">
                        @method('delete')
                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                            {{ __('app.delete') }}
                        </button>
                    </form>

                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No products defined.</td> 
        </tr>
        @endforelse
    </tbody>
</table>


{{ $products->withQueryString()->links('pagination::bootstrap-5') }}


@endsection