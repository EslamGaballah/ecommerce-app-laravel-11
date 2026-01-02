<?php

namespace App\Http\Controllers\Front;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       $comments = Comment::whereNull('parent_id')
            ->with('replies')
            ->latest()
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Comment $comment)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request)
    {
        $comment = $request->validated();

        $comment['user_id'] = auth()->id();

        Comment::create($comment);

        // return response()->json($data);

        return response()->json([
            'id' => $comment->id,
            'body' => $comment->body,
            'parent_id' => $comment->parent_id,
            'user' => $comment->user->name,
        ]);

        // return response()->json([
        // 'message' => 'Comment created successfully',
        // ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $data = $request->validated();
        $comment->update([
        'comment' => $request->comment,
    ]);

        return response()->json([
        'message' => 'Comment updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(comment $comment)
    {
        //
    }
}
