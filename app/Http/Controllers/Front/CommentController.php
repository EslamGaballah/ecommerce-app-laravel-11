<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
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
    public function index( )
    {
      
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
        $data = $request->validated();

        $data['user_id'] = auth()->id();

        $comment= Comment::create($data);

         $comment->load('user', 'replies');

        if ($request->expectsJson()) {
            $html = view('partials._comment', compact('comment'))->render();
            
            return response()->json([
                'html' => $html
                ]);
        }

        return back();
   
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
            'body' => $data['body']
        ]);

        return response()->json([
        'message' => 'Comment updated successfully',
        'body' => $comment->body,
        'id' => $comment->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Comment $comment)
    {
        try {
            $comment->delete();

            return response()->json([
                'message' => 'Deleted',
                'id' => $comment->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
