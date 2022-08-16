<?php

namespace App\Http\Controllers\Api;

use App\Services\PusherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Models\Follower;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function store(Request $request)
    {
        $task = Task::find($request->task_id);
        if ($followers = Follower::getFollowers($task->id, 'task')) {
            foreach ($followers as $follower) {
                Follower::newComment($follower, $request, $task);
            }
        }
        PusherService::pushCommentMentionData($request);

        $system_comment = Comment::sendSystemComment($task->id, 'comment', $request->text);

        if ($system_comment) {
            return ['success' => $system_comment];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Comment                  $comment
     *
     * @return array
     */
    public function update(Comment $comment, Request $request)
    {
        $comment->text = $request->comment_text;

        if ($comment->save()) {
            return ['success' => $comment];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Comment $comment
     *
     * @return array
     */
    public function destroy(Comment $comment)
    {
        $comment->is_visible = 0;

        if ($comment->save()) {
            return ['success' => $comment];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * @param Comment $comment
     *
     * @return array
     */
    public function toggleArchive(Comment $comment)
    {
        $comment->is_arhive = !$comment->is_arhive;

        if ($comment->save()) {
            return ['success' => $comment];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function allToArchive(Request $request)
    {
        $comments_ids = $request->comments_ids;
        foreach ($comments_ids as $key => $comment_id) {
            $comment            = Comment::find($comment_id);
            $comment->is_arhive = 1;
            $comment->save();
            $result[$key] = $comment;
        }

        if ($result) {
            return ['success' => $result];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function clearArchive()
    {
        $comments = Comment::where('recipient_id', Auth::user()->id)->where('is_arhive', 1)->get();
        foreach ($comments as $comment) {
            $comment->is_visible = 0;
            $comment->save();
        }

        if ($comments) {
            return ['success' => $comments];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
