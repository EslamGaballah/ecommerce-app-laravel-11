 <ul class="inner-sub-category">
    @foreach ($children  as $child )
            <li><a href="{{ route('dashboard.categories.show', $category->slug) }}">
                {{ $child->name }}
                <i class="lni lni-chevron-right"></i></a>
                
                @if ($child->childrenRecursive->isNotEmpty())
                {{-- @if ($category->childern->count()) --}}
                     @include('partials.category-tree', [
                        'children' => $child->childrenRecursive
                    ])
                @endif
            </li>
    @endforeach
</ul>