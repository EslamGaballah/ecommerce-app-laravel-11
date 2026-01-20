<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Post;
use App\Http\Controllers\Controller;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Models\Image;
use App\Traits\UploadImageTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Throwable;

class PostController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('user')->paginate();

        // return view('dashboard.posts.index', compact('posts'));
        return view('front.blog.blog-grid-sidebar', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Post $post)
    {
        return view('dashboard.posts.create', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request, Post $post )
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($request->post('title'), '-', null);

        DB::beginTransaction();
        try 
        {
            $post = Post::create($data);

            // create images
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $index => $imageFile) {

                        $path = $this->uploadImage($imageFile, 'posts');

                        $post->images()->create([ // image table with morph
                            'image' => $path,
                            'alt' => $request->image_alt[$index] ?? null
                        ]);
                    }
                }

                 DB::commit();

             } catch (Throwable $e) {
                    DB::rollBack();
                    throw $e;
                    return back()->with('error', 'Failed to create post');
            }

        return Redirect::route('dashboard.posts.index')->with('success', 'post created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->load(['tags', 'images']);
        $comments = $post->comments()->whereNull('parent_id')->with( 'replies.user', 'user')->get();

        return view('front.blog.blog-single', compact('post', 'comments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('dashboard.posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        // foreach ($request->existing_image_alt as $imageId => $alt) {
        //     Image::where('id', $imageId)->update(['alt' => $alt]);
        // }
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($request->post('title'), '-', null);

        DB::beginTransaction();
        try 
        {
            $post->update($data);

            // create images
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $index => $imageFile) {

                        $path = $this->uploadImage($imageFile, 'posts');

                        $post->images()->create([ // image table with morph
                            'image' => $path,
                            'alt' => $request->image_alt[$index] ?? null
                        ]);
                    }
                }

                 DB::commit();

             } catch (Throwable $e) {
                    DB::rollBack();
                    throw $e;
                    return back()->with('error', 'Failed to update post');
            }

             return back()->with('success', 'post updated');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // dd($post);
         DB::beginTransaction();

        try {
            // Delete Images
            if ($post->images->isNotEmpty()) {
                foreach ($post->images as $image) {
                    $this->deleteImage($image->image);
                }
            // Delete image from database
            $post->images()->delete();
        }
        
        $post->delete();
        
        DB::commit();
        
        } catch (Throwable $e) {
            DB::rollBack();
            return Redirect::route('posts.index')
                ->with('error', 'Failed to delete post');
        }

        return back()->with('success', 'Post deleted successfully');

    }
}
