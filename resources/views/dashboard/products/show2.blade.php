@extends('layouts.dashboard')

@section('title', $product->name)

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __('app.products') }}</li>
<li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5>{{ __('app.product_details') }}: {{ $product->name }}</h5>
    </div>

    <div class="card-body">
        <div class="row">

            {{-- صورة المنتج --}}
            <div class="col-md-3">

                @php
                    $image = $product->images->first()?->image ?? $product->main_image;
                @endphp

                <img src="{{ asset('storage/' . $image) }}"
                    class="img-fluid rounded border shadow-sm"
                    style="max-height:250px;width:100%;object-fit:contain">
            </div>

            <div class="col-md-9">

                <table class="table table-sm table-borderless">

                    <tr>
                        <th width="150">{{ __('app.category') }}:</th>
                        <td>{{ $product->category->name }}</td>
                    </tr>

                    <tr>
                        <th>{{ __('app.status') }}:</th>
                        <td>
                            <span class="badge bg-{{ $product->status->color() }}">
                                {{ $product->status->label() }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>{{ __('app.product_type') }}:</th>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst($product->product_type->value) }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>{{ __('app.total_quantity') }}:</th>
                        <td><strong>{{ $product->total_quantity }}</strong></td>
                    </tr>

                </table>

                <p>
                    <strong>{{ __('app.description') }}</strong><br>
                    {{ $product->description }}
                </p>

            </div>
        </div>
    </div>
</div>


{{-- ============================= --}}
{{-- SIMPLE PRODUCT --}}
{{-- ============================= --}}

@if($product->product_type === 'simple')

<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5>{{ __('app.product_information') }}</h5>
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4">
                <strong>{{ __('app.price') }}</strong><br>
                {{ Currency::format($product->price) }}
            </div>

            <div class="col-md-4">
                <strong>{{ __('app.compare_price') }}</strong><br>
                @if($product->compare_price)
                    <del>{{ Currency::format($product->compare_price) }}</del>
                @else
                    -
                @endif
            </div>

            <div class="col-md-4">
                <strong>{{ __('app.stock') }}</strong><br>
                {{ $product->stock }}
            </div>

        </div>

        {{-- صور المنتج --}}
        <hr>

        <div class="row">

            @foreach($product->images as $image)

                <div class="col-md-2 mb-3">
                    <img src="{{ asset('storage/'.$image->image) }}"
                        class="img-fluid rounded border shadow-sm"
                        style="height:120px;object-fit:cover">
                </div>

            @endforeach

        </div>

    </div>
</div>

@endif


{{-- ============================= --}}
{{-- VARIABLE PRODUCT --}}
{{-- ============================= --}}

@if($product->product_type === 'variable')

<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5>{{ __('app.product_variations') }}</h5>
    </div>

    <div class="card-body p-0">

        <table class="table table-hover mb-0">

            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>SKU</th>
                    <th>{{ __('app.attributes') }}</th>
                    <th>{{ __('app.price') }}</th>
                    <th>{{ __('app.compare_price') }}</th>
                    <th>{{ __('app.quantity') }}</th>
                    <th>{{ __('app.primary') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>

            <tbody>

                @foreach($product->variations as $variation)

                <tr class="variation-row">

                    <td>
                        @if($variation->images->first())
                            <img src="{{ asset('storage/'.$variation->images->first()->image) }}"
                                width="50"
                                height="50"
                                class="rounded border"
                                style="object-fit:cover">
                        @endif
                    </td>

                    <td><code>{{ $variation->sku }}</code></td>

                    <td>
                        @foreach($variation->values as $value)

                            <span class="badge bg-light text-dark border">
                                {{ $value->attribute->name }} : {{ $value->value }}
                            </span>

                        @endforeach
                    </td>

                    <td>{{ Currency::format($variation->price) }}</td>

                    <td>
                        @if($variation->compare_price)
                            <del class="text-danger">
                                {{ Currency::format($variation->compare_price) }}
                            </del>
                        @endif
                    </td>

                    <td>{{ $variation->quantity }}</td>

                    <td>
                        @if($variation->is_primary)
                            <span class="badge bg-success">
                                {{ __('app.primary') }}
                            </span>
                        @endif
                    </td>

                    <td>

                        <button
                            class="btn btn-danger btn-sm delete-variation"
                            data-url="{{ route('dashboard.products.variation.delete',$variation->id) }}">
                            {{ __('app.delete') }}
                        </button>

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>
</div>

@endif

@endsection