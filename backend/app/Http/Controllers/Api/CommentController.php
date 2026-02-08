<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a comment for a task.
     */
    public function store(StoreCommentRequest $request)
    {
        $task = Task::findOrFail($request->task_id);

        $this->authorize('create', [Comment::class, $task]);

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->withProperties(['task_id' => $task->id])
            ->log('comment_created');

        return new CommentResource(
            $comment->load('user')
        );
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        activity()
            ->causedBy(request()->user())
            ->performedOn($comment)
            ->withProperties(['task_id' => $comment->task_id])
            ->log('comment_deleted');

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}

