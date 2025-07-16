<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();
       $categories = Category::with('parent')
       ->filter($request->query())
       ->paginate(5); // شغالة
        
    //    $categories = Category::with('parent')->get();  //    لا تعمل لاستدعاء (links )  فى blade
       return view('dashboard.categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Category::class);

        $parents = Category::all();
        $category = new Category();
       
        // dd();
        return view('dashboard.categories.create',compact('category', 'parents'));

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

            return Redirect::route('dashboard.categories.index')
            ->with('success', 'Category created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        // dd( $category);
        
        return view('dashboard.categories.show',compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        $parents = Category::where('id', '<>', $id)
            ->where(function($query) use($id){
            $query->whereNull('parent_id')
                    ->orWhere('parent_id', '<>', $id);
            })->get();
            // dd($parents);

        return view('dashboard.categories.edit',compact('category', 'parents'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->all();
        $category->update($data);

        return Redirect::route('dashboard.categories.index')
            ->with('success', 'Category updated!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
    }
}
