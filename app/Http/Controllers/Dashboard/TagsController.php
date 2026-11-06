<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagsController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::withCount('products')
            ->filter($request->query())
            ->orderByDesc('products_count')
            ->paginate();

        return view('dashboard.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('dashboard.tags.create', ['tag' => new Tag()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255', 'unique:tags,name_en'],
        ]);

        $data['slug'] = Str::slug($data['name_en']);

        Tag::create($data);

        return redirect()->route('dashboard.tags.index')->with('success', __('app.created_successfully'));
    }

    public function show(Tag $tag)
    {
        $products = $tag->products()->paginate(10);

        return view('dashboard.tags.show', compact('tag', 'products'));
    }

    public function edit(Tag $tag)
    {
        return view('dashboard.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255', Rule::unique('tags', 'name_en')->ignore($tag)],
        ]);

        $data['slug'] = Str::slug($data['name_en']);

        $tag->update($data);

        return redirect()->route('dashboard.tags.index')->with('success', __('app.updated_successfully'));
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('dashboard.tags.index')->with('success', __('app.deleted_successfully'));
    }
}
