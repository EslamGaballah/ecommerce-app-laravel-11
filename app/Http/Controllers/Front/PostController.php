<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private const SECTION_SIZE = 4;

    public function index(Request $request)
    {
        $posts = Post::with(['user', 'images'])->published()->latest()->paginate(6);

        // The list has no article of its own, so "similar" follows the last one read.
        $lastRead = Post::published()->find($request->session()->get('last_read_post'));

        return view('front.blog.blog-grid-sidebar', [
            'posts' => $posts,
            'sections' => $this->sections($lastRead),
        ]);
    }

    public function show(Request $request, Post $post)
    {
        // Staff can preview unpublished posts.
        if ($post->status !== 'published') {
            abort_unless(auth()->user()?->can('access-dashboard'), 404);
        } else {
            $this->countRead($request, $post);
        }

        $post->load(['tags', 'images']);
        $comments = $post->comments()->whereNull('parent_id')->with( 'replies.user', 'user')->get();

        return view('front.blog.blog-single', [
            'post' => $post,
            'comments' => $comments,
            'sections' => $this->sections($post, [$post->id]),
        ]);
    }

    /** Once per visitor session, without touching the post's updated_at. */
    private function countRead(Request $request, Post $post): void
    {
        $request->session()->put('last_read_post', $post->id);

        $read = $request->session()->get('read_posts', []);

        if (in_array($post->id, $read, true)) {
            return;
        }

        Post::whereKey($post->id)->toBase()->increment('views_count');

        $request->session()->put('read_posts', [...$read, $post->id]);
    }

    /**
     * The side lists. A post shown in one is left out of the next, so the same
     * titles are not repeated down the column.
     *
     * @param  array<int, int>  $exclude
     * @return array{similar: \Illuminate\Support\Collection, mostRead: \Illuminate\Support\Collection, latest: \Illuminate\Support\Collection}
     */
    private function sections(?Post $similarTo, array $exclude = []): array
    {
        $list = function ($scope) use (&$exclude) {
            $posts = $scope(Post::published()->with('images')->whereKeyNot($exclude))
                ->limit(self::SECTION_SIZE)
                ->get();

            $exclude = [...$exclude, ...$posts->modelKeys()];

            return $posts;
        };

        return [
            'similar' => $similarTo ? $list(fn ($query) => $query->similarTo($similarTo)) : collect(),
            'mostRead' => $list(fn ($query) => $query->mostRead()),
            'latest' => $list(fn ($query) => $query->newestFirst()),
        ];
    }
}
