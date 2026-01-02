<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->filter($request->query())
            ->orderByDesc('products_count')
            ->paginate(5); // شغالة
        
    //    $categories = Category::with('parent')->get();  //    لا تعمل لاستدعاء (links )  فى blade
       return view('dashboard.categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Category $category)
    {
        $parents = Category::all();
       
        return view('dashboard.categories.create',compact( 'category', 'parents'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
            $data = $request->validated();
            $data['slug'] = str::slug($request->post('name'));

             Category::create($data);

            return Redirect::route('dashboard.categories.index')
            ->with('success', 'Category created!');
    }

    /**
     * Display the specified resource.
     */
    public function show (Category $category)
    {
        return view('dashboard.categories.show',compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit (Category $category)
    {
        $this-> authorize('edit-catggories', $category);
        $parents = Category::where('id', '<>', $category->id)
            ->where(function($query) use($category){
            $query->whereNull('parent_id')
                    ->orWhere('parent_id', '<>', $category->id);
            })->get();

        return view('dashboard.categories.edit',compact('category', 'parents'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update (UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['slug'] = str::slug($request->post('name'));

        $category->update($data);

        return Redirect::route('dashboard.categories.index')
            ->with('success', 'Category updated!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (Category $category)
    {
        $this-> authorize('update', $category);

        $category->delete();
    }
}
