@extends('layouts.dashboard')

@section('title', $product->name)

@section('breadcrumb')
@parent
<li class="breadcrumb-item active">{{ __('app.products') }}</li>
<li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')

{{-- <table class="table">
    <thead>
        <tr>
            <th></th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.description') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.variations') }}</th>
            <th>{{ __('app.quantity') }}</th>
            <th>{{ __('app.price') }}</th>
            <th>{{ __('app.status') }}</th>
            <th> {{ __('app.created_at') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
        @endphp
        <tr>
            <td><img src="{{ asset('storage/' . $product->image) }}" alt="" height="50"></td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->description }}</td>
            <td>{{ $product->category->name }}</td>
            <td>
                
            </td>
            <td>{{ $product->quantity }}</td>
            <td>{{ $product->price }}</td>
            <td>{{ $product->status }}</td>
            <td>{{ $product->created_at }}</td>
        </tr>
       
    </tbody>
</table> --}}

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5>{{ __('app.product_details') }}: {{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            
            <div class="col-md-3">
                @php
                    // استخراج المسار من العلاقة المحملة
                    $currentPrimaryImage = $product->primaryVariation?->images?->first()?->image;
                @endphp

                <img src="{{ asset('storage/' . ($currentPrimaryImage ?? $product->image)) }}" 
                    class="img-fluid rounded border shadow-sm" 
                    alt="{{ $product->name }}"
                    style="max-height: 250px; width: 100%; object-fit: contain;"
                >
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
                        <th>{{ __('app.total_quantity') }}:</th>
                        <td><strong>{{ $product->total_quantity }}</strong></td> {{-- الـ Accessor الذي برمجناه سابقاً --}}
                    </tr>
                </table>
                <p><strong>{{ __('app.description') }}:</strong><br>{{ $product->description }}</p>
            </div>
        </div>
    </div>
</div>

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
                    <th>{{ __('app.attributes') }}</th> {{-- اللون، المقاس، إلخ --}}
                    <th>{{ __('app.price') }}</th>
                    <th>{{ __('app.compare_price') }}</th>
                    <th>{{ __('app.quantity') }}</th>
                    <th>{{ __('app.primary') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($product->variations as $variation)
                <tr class="variation-row">

                    <td>
                        @if($variation->images && $variation->images->first())
                            <img src="{{ asset('storage/' . $variation->images->first()->image) }}" 
                                alt="Variation Image" 
                                width="50" height="50" 
                                class="rounded border shadow-sm shadow-hover"
                                style="object-fit: cover; cursor: pointer;"
                                onclick="window.open(this.src)">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded border" style="width: 50px; height: 50px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        @endif
                    </td>

                    <td><code>{{ $variation->sku }}</code></td>
                    <td>
                        @foreach($variation->values as $value)
                            <span class="badge border text-dark bg-light">
                                {{ optional($value->attribute)->name }}: {{ $value->value }}                            
                            </span>
                        @endforeach
                    </td>
                    <td>{{ Currency::format($variation->price) }}</td>
                    <td>
                        @if($variation->compare_price)
                            <del class="text-danger">{{ Currency::format($variation->compare_price) }}</del>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($variation->quantity <= 5)
                            <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> {{ $variation->quantity }}</span>
                        @else
                            {{ $variation->quantity }}
                        @endif
                    </td>
                    <td>
                        @if($variation->is_primary)
                            <span class="badge bg-success"><i class="fas fa-check"></i> {{ __('app.primary') }}</span>
                        @endif
                    </td>
                    <td>
                        {{-- <form method="POST"
                            action="{{ route('dashboard.products.variation.delete', $variation->id) }}"
                            onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">
                               {{__('app.delete')}}
                            </button>
                        </form> --}}
                        <button
                            class="btn btn-danger btn-sm delete-variation"
                            {{-- data-id="{{ $variation->id }}"> --}}
                            data-url="{{ route('dashboard.products.variation.delete', $variation->id) }}">
                            
                           {{__('app.delete')}}
                        </button>



                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">{{ __('No variations available') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
<script>

document.querySelectorAll('.delete-variation').forEach(btn => {
    btn.onclick = function () {

        if (!confirm('تأكيد حذف الـ Variation؟')) return;
        
       const url = this.dataset.url;
       
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',

            }
        })
        .then(res => res.json())
        .then(() => {
            this.closest('.variation-row').remove();
        });
    }
});
</script>

    
@endpush