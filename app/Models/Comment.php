<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 11.04.2018
 * Time: 10:47
 */

namespace App\Models;

use App\Helpers\HtmlHelper;

use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    protected $fillable = [
        'task_id',
        'type',
        'text',
        'author_id',
        'recipient_id',
        'conversation_id',
        'unique_id',
        'is_arhive',
    ];

    protected $appends = [
        'class',
        'date',
        'is_liked',
        'likes_count',
        'author_name'
    ];

    //get single model

    public function author()
    {
        return $this->hasOne(User::class, 'id', 'author_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    //get multiple model

    public function likes()
    {
        return $this->morphToMany(Like::class, 'likeble');
    }

    //get attributes

    public function getAuthorNameAttribute()
    {
        return $this->type == 'comment' && is_object($this->author) ? $this->author->name : 'Kodo CRM';
    }

    public function getClassAttribute()
    {
        return $this->type == 'comment' ? 'comment' : 'system';
    }

    public function getDateAttribute()
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    public function getIsLikedAttribute()
    {
        return Like::isLiked(Auth::user()->id, 'comment', $this->id);
    }

    public function getLikesCountAttribute()
    {
        return Like::where('post_id', $this->id)->where('type', 'comment')->count();
    }

    /*public function getTextAttribute($text)
    {
        return HtmlHelper::findLinks($text);
    }*/

    //

    public static function getComments($user_id, $is_arhive)
    {
        return self::where('recipient_id', $user_id)
            ->where('is_visible', 1)
            ->where('is_arhive', $is_arhive)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public static function sendSystemComment($task_id, $type, $text)
    {
        $system_comment               = new Comment;
        $system_comment->task_id      = $task_id;
        $system_comment->type         = $type;
        $system_comment->recipient_id = 0;
        $system_comment->text         = $text;
        $system_comment->is_arhive    = 0;
        $system_comment->author_id    = $type == 'comment' ? Auth::user()->id : null;
        $system_comment->save();

        return $system_comment;
    }

    public static function sendPersonalComment($post_id, $type, $text, $user_id, $comment_category)
    {
        $comment = new Comment;
        switch ($comment_category){
            case 'task':
                $comment->task_id = $post_id;
                break;
            case 'conversation':
                $comment->conversation_id = $post_id;
                break;
        }
        $comment->type         = $type;
        $comment->recipient_id = $user_id;
        $comment->text         = $text;
        $comment->is_arhive    = 0;
        $comment->author_id    = $type == 'comment' ? Auth::user()->id : null;
        $comment->save();

        return $comment;
    }
}