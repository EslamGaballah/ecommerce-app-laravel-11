<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Tag $tag)
    {
        Tag::withCount('products')
            ->filter($request->query())
            ->orderByDesc('products_count')
            ->paginate();
        return view('dashboard.tags.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Tag $tag)
    {
        return view('dashboard.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   $data = $request->validate([
        'name' => 'string|unique|max:255',
        'slug' => 'unique'
    ]);

        $data['slug'] = Str::slug($request('name'));

        Tag::create($data);

        return back()->with('success', 'Tag created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        $products = $tag->products()->paginate(10);

        return view('dashboard.tags.show',compact('tag', 'products'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        return view('dashboard.tags.edit',compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
        'name' => 'string|unique|max:255',
        'slug' => 'unique'
    ]);

        $data['slug'] = Str::slug($request('name'));

        $tag->update($data);

        return back()->with('success', 'Tag updated successfully!');
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
    }
}
