<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->paginate(5);

        return view('front.blog.blog-grid-sidebar', compact('posts'));
    }

    public function show(Post $post)
    {
        $post->load(['tags', 'images']);
        $comments = $post->comments()->whereNull('parent_id')->with( 'replies.user', 'user')->get();

        return view('front.blog.blog-single', compact('post', 'comments'));
    }

}
