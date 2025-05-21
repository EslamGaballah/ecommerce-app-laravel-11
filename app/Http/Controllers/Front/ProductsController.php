<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{

    public function index() {
        $products = Product::paginate(5);
        return view('front.products.index', compact('products'));
    }
    public function show( $id)
    {
        // if ($product->status != 'active') {
        //     abort(404);
        // }
        $product = Product::findOrFail($id);

        // dd($product);

        return view('front.products.show', compact('product'));
    }
}
