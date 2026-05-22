<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('parent')->paginate();
        return response()->json($categories, 201);
    }
    public function create()
    {
        $parents = Category::all();
        $category = new Category();
        $category = Category::with('parents');
        return response()->json($category,201,[
            'location' => route('categories.create')
        ]);
        
        // dd($category);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'in:active,archived',
            'parent_id' => 'nullable'
        ]);

        $request->merge([
            'slug' => str::slug($request->post('name'))
        ]);

        $category = Category::create($request->all());

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     $category = Category::findOrFail($id);
    //     return response()->json($category);

    // }
    public function show(Category $category)
    {
        return response()->json($category);

    }

    public function edit(Category $category)
    {

        return response()->json($category);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        info($request);
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'in:active,archived',
            'parent_id' => 'nullable'

        ]);
        
        $request->merge([
            'slug' => str::slug($request->post('name'))
        ]);

        $category = Category::findOrFail($id);
        $category->update($request->all());

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $category = Category::findOrFail($id)->deleteOrFail();
        $category = Category::destroy($id);

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);

    }
}
