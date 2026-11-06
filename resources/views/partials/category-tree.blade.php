 <ul class="inner-sub-category">
    @foreach ($children  as $child )
            <li><a href="{{ route('products.index', ['category' => $child->id]) }}">
                {{ $child->name }}
                @if ($child->childrenRecursive->isNotEmpty())
                    <i class="lni lni-chevron-right"></i>
                @endif
                </a>

                @if ($child->childrenRecursive->isNotEmpty())
                {{-- @if ($category->childern->count()) --}}
                     @include('partials.category-tree', [
                        'children' => $child->childrenRecursive
                    ])
                @endif
            </li>
    @endforeach
</ul>