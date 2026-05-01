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

<form action="{{ request()->url() }}" method="get" 
    {{-- class="d-flex flex-wrap justify-content-between mb-4"> --}}
    class="d-flex flex-wrap align-items-center flex-md-nowrap gap-2 mb-4 overflow-auto">

    {{-- 🔍 search --}}
    <x-form.input 
        name="search" 
        placeholder="Search..." 
        {{-- class="mx-2 mb-2" --}}
        class="mb-0"
        style="width: 180px;"
        :value="request('search')" 
    />

    {{-- 📂 category --}}
    <select name="category" 
        {{-- class="form-control mx-2 mb-2"> --}}
        class="form-select mb-0" style="width: 160px;">
        <option value="">{{ __('app.categories') }}</option>
        @foreach($categories as $category)
            <option 
                value="{{ $category->id }}"
                @selected(request('category') == $category->id)
            >
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    {{-- 🏷 brand --}}
    <select name="brand" 
        {{-- class="form-control mx-2 mb-2"> --}}
        class="form-select mb-0" style="width: 160px;">
        <option value="">{{ __('app.brands') }}</option>
        @foreach($brands as $brand)
            <option 
                value="{{ $brand->id }}"
                @selected(request('brand') == $brand->id)
            >
                {{ $brand->name }}
            </option>
        @endforeach
    </select>

    {{-- ⚡ status --}}
    <select name="status" 
        {{-- class="form-control mx-2 mb-2"> --}}
        class="form-select mb-0" style="width: 140px;">
        <option value="">{{ __('app.all') }}</option>
        @foreach(\App\Enums\ProductStatus::cases() as $status)
            <option 
                value="{{ $status->value }}"
                @selected(request('status') == $status->value)
            >
                {{ $status->label() }}
            </option>
        @endforeach
    </select>

    {{-- 🔽 sort --}}
    <select name="sort" 
        {{-- class="form-control mx-2 mb-2"> --}}
        class="form-select mb-0" style="width: 180px;">
        <option value="">{{ __('app.sort_by') }}</option>

        <option value="low_price"  @selected(request('sort') == 'low_price')>
           {{ __('app.low_price') }}
        </option>

        <option value="high_price" @selected(request('sort') == 'high_price')>
           {{ __('app.high_price') }}
        </option>

        <option value="newest" @selected(request('sort') == 'newest')>
           {{ __('app.newst') }}
        </option>

        <option value="oldest" @selected(request('sort') == 'oldest')>
           {{ __('app.oldest') }}
        </option>
    </select>

    {{-- 🔘 submit --}}
    <button class="btn btn-dark mx-2 mb-0">
        {{ __('app.filter') }}
    </button>

</form>



<table class="table">
    <thead>
        <tr>
            {{-- <th>#</th> --}}
            <th>ID</th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.stock') }}</th>
            <th>{{ __('app.price') }}</th>
            {{-- <th>{{ __('app.compare_price') }}</th> --}}
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.created_at') }}</th>
            <th colspan="2">{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            {{-- <td>
                @if ($product->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $product->images->first()->image) }}" 
                        alt="" height="50">
                @else
                    <span>No Image</span>
                @endif
            </td> --}}
            <td>{{ $product->id }}</td>
            <td> <a href="{{ route('dashboard.products.show', $product->id) }}">{{ $product->name }}</td>
            <td>{{ $product->category->name }}</td>
            <td>@if($product->stock)
                    {{ $product->stock}}
                @else
                    {{ $product->total_quantity }}
                @endif

            </td>

            <td>
                @if ($product->price && $product->compare_price)
                     <span class="fw-bold">
                        {{ $product->price }}
                    </span>
                    <span class="text-muted text-decoration-line-through">
                        {{ $product->compare_price }}
                    </span>
                    
                @elseif($product->primaryVariation && $product->primaryVariation->compare_price > $product->price)
                    <span class="fw-bold">
                        {{ $product->primaryVariation->price }}
                    </span>
                    <span class="text-muted text-decoration-line-through">
                        {{ $product->primaryVariation->compare_price }}
                    </span>
                @endif
                
                
            </td>

            <td>
                <span class="badge bg-{{ $product->status->color() }} badge-{{ $product->id }}">
                    {{ $product->status->label() }}
                </span>
            </td>
            <td>{{ $product->created_at }}</td>
            <td>
                <a href="{{ route('dashboard.products.show', $product->id) }}" class="btn btn-sm btn-outline-success">{{ __('app.show') }}</a>
            </td>
            <td>
                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-sm btn-outline-success">{{ __('app.edit') }}</a>
            </td>
            <td>
                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="post">
                    @csrf
                    <!-- Form Method Spoofing -->
                    <input type="hidden" name="_method" value="delete">
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.delete') }}</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9">No products defined.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }} 
{{ $products->links('pagination::bootstrap-5') }}  --}}


{{ $products->withQueryString()->appends(['search' => 1])->links('pagination::bootstrap-5') }}


@endsection