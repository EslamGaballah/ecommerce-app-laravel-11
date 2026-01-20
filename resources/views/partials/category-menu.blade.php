 <div class="mega-category-menu">
    <span class="cat-button"><i class="lni lni-menu"></i>{{ __('app.categories') }}</span>
    <ul class="sub-category">
        @foreach ($categories as $category )
            <li><a href="{{ route('dashboard.categories.show', $category->id) }}">
                {{ $category->name }}
                <i class="lni lni-chevron-right"></i></a>
                
                @if ($category->childrenRecursive->isNotEmpty())
                {{-- @if ($category->childern->count()) --}}
                     @include('partials.category-tree', [
                        'children' => $category->childrenRecursive
                    ])
                @endif
            </li>
        @endforeach
    </ul>
</div>