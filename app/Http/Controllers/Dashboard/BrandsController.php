<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Brand;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::withCount('products')->paginate();
        return view('dashboard.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brand = new Brand();

        return view('dashboard.brands.create', compact('brand'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255'
        ]);
        // dd($request->all());
        Brand::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'Brand created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        $brand = Brand::with(['products.category'])->get();

        return view('dashboard.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        // $brand->load('products.category');
        return view('dashboard.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        // $brand = Brand::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255'
        ]);

        $brand->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'Brand updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully');
    }
}
