<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 20.08.2018
 * Time: 14:09
 */

namespace App\Models;


use Illuminate\Support\Facades\Auth;


class Like extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'post_id'
    ];

    //get multiple model

    public function comments()
    {
        return $this->morphedByMany(Comment::class, 'likeble');
    }

    public function conversations()
    {
        return $this->morphedByMany(Conversation::class, 'likeble');
    }

    public function files()
    {
        return $this->morphedByMany(File::class, 'likeble');
    }

    public function messages()
    {
        return $this->morphedByMany(Message::class, 'likeble');
    }

    public function projects()
    {
        return $this->morphedByMany(Project::class, 'likeble');
    }

    public function tasks()
    {
        return $this->morphedByMany(Task::class, 'likeble');
    }

    //

    public static function isLiked($user_id, $type, $post_id)
    {
        if (Like::where('user_id', $user_id)
            ->where('type', $type)
            ->where('post_id', $post_id)
            ->first()) {
            return true;
        } else {
            return false;
        }
    }

    public static function getLike($user_id, $type, $post_id)
    {
        $like = Like::where('user_id', $user_id)
            ->where('type', $type)
            ->where('post_id', $post_id)
            ->first();

        return $like;
    }

    public static function getLikedComments($user)
    {
        $likes    = Like::where('user_id', $user->id)->where('type', 'comment')->get();
        $comments = [];
        if($likes->count()){
            foreach ($likes as $key => $like) {
                if($like->comments->count()){
                    $comments[$key] = $like->comments[0];
                }
            }
        }

        return $comments;
    }

    public static function getLikedConversations($user)
    {
        $likes         = Like::where('user_id', $user->id)->where('type', 'conversation')->get();
        $conversations = [];
        if($likes->count()){
            foreach ($likes as $key => $like) {
                if($like->conversations->count()){
                    $conversations[$key] = $like->conversations[0];
                }
            }
        }

        return $conversations;
    }

    public static function getLikedFiles($user)
    {
        $likes = Like::where('user_id', $user->id)->where('type', 'file')->get();
        $files = [];
        if($likes->count()){
            foreach ($likes as $key => $like) {
                if($like->files->count()){
                    $files[$key] = $like->files[0];
                }
            }
        }

        return $files;
    }

    public static function getLikedMessages($user)
    {
        $likes    = Like::where('user_id', $user->id)->where('type', 'message')->get();
        $messages = [];
        if($likes->count()){
            foreach ($likes as $key => $like) {
                if($like->messages->count()){
                    $messages[$key] = $like->messages[0];
                }
            }
        }

        return $messages;
    }

    public static function getLikedProjects($user)
    {
        $likes    = Like::where('user_id', $user->id)->where('type', 'project')->get();
        $projects = [];
        if($likes->count()){
            foreach ($likes as $key => $like) {
                if($like->projects->count()){
                    $projects[$key] = $like->projects[0];
                }
            }
        }

        return $projects;
    }

    public static function getLikedTasks($user)
    {
        $likes = Like::where('user_id', $user->id)->where('type', 'task')->get();
        $tasks       = [];
        if($likes->count()){
            foreach ($likes as $key => $like) {
                if($like->tasks->count()){
                    $tasks[$key] = $like->tasks[0];
                }
            }
        }

        return $tasks;
    }

    public static function attachLike($request){
        $like          = new Like;
        $like->user_id = Auth::user()->id;
        $like->type    = $request->type;
        $like->post_id = $request->post_id;
        $like->save();
        switch ($request->type) {
            case 'comment' :
                Comment::find($request->post_id)->likes()->attach($like->id);
                break;
            case 'conversation' :
                Conversation::find($request->post_id)->likes()->attach($like->id);
                break;
            case 'file' :
                File::find($request->post_id)->likes()->attach($like->id);
                break;
            case 'message' :
                Message::find($request->post_id)->likes()->attach($like->id);
                break;
            case 'project' :
                Project::find($request->post_id)->likes()->attach($like->id);
                break;
            case 'task' :
                Task::find($request->post_id)->likes()->attach($like->id);
                break;
        }

        return 'liked';
    }

    public static function detachLike($request)
    {
        $like = Like::getLike(Auth::user()->id, $request->type, $request->post_id);
        switch ($request->type) {
            case 'comment' :
                Comment::find($request->post_id)->likes()->detach($like->id);
                break;
            case 'conversation' :
                Conversation::find($request->post_id)->likes()->detach($like->id);
                break;
            case 'file' :
                File::find($request->post_id)->likes()->detach($like->id);
                break;
            case 'message' :
                Message::find($request->post_id)->likes()->detach($like->id);
                break;
            case 'project' :
                Project::find($request->post_id)->likes()->detach($like->id);
                break;
            case 'task' :
                Task::find($request->post_id)->likes()->detach($like->id);
                break;
        }
        $like->delete();

        return 'unliked';
    }
}