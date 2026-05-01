<?php

namespace App\Filters;

use Illuminate\Http\Request;

class ProductFilter
{
     protected $request;

    protected $filters = [
        'search',
        'price',
        'min_price',
        'max_price',
        'category',
        'brand',
        'status',
        'sort_by',
    ];

    /**
     * Create a new class instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // public function apply($query)
    // {
    //     foreach ($this->filters as $filter) {
    //         if (!$this->request->filled($filter)) continue;

    //         if (method_exists($this, $filter)) {
    //             $query = $this->$filter($query, $this->request->$filter);
    //         }
    //     }

    //     return $query;
    // }

    public function apply($query)
    {
        foreach ($this->filters as $filter) {

            $value = $this->request->input($filter);

            // skip لو null أو array فاضي
            if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }

            if (method_exists($this, $filter)) {
                $query = $this->$filter($query, $value);
            }
        }

        return $query;
    }

    // --------------------------------------
    // filters
    // --------------------------------------
    public function search($query, $value)
    {
       return $query->where(function ($q) use ($value) {
            $q->where('name_en', 'like', "%$value%")
            ->orWhere('name_ar', 'like', "%$value%");
        });
    }
    
    public function price($query, $value)
    {
        return $query->where('price', '<=', $value);
    }

    public function min_price($query, $value)
    {
        return $query->where('price', '>=', $value);
    }

    public function max_price($query, $value)
    {
        return $query->where('price', '<=', $value);
    }

    public function category($query, $value)
    {
       return $query->whereIn('category_id', (array) $value);  // multi-categories
    }

    public function brand($query, $value)
    {
        return $query->whereIn('brand_id', (array) $value); // multi-brands
    }

    public function status($query, $value)
    {
        return $query->where('products.status', $value);
    }

    // public function sort($query, $value)
    // {
    //     return $query->sortBy($value);       // from model product
    // }

    public function sort_by($query, $value)
    {
        return match ($value) {
            'low_price'  => $query->orderBy('price', 'asc'),
            'high_price' => $query->orderBy('price', 'desc'),
            'newest'     => $query->orderBy('created_at', 'desc'),
            'oldest'     => $query->orderBy('created_at', 'asc'),
            default      => $query->orderBy('id', 'desc'),
        };
    }
    
}
