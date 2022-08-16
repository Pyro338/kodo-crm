<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 25.05.2018
 * Time: 11:36
 */

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use App\Helpers\HtmlHelper;

class Conversation extends Model
{
    protected $fillable = [
        'title',
        'owner_id',
        'is_visible',
        'workspace_id',
        'task_id'
    ];

    protected $appends = [
        'text_preview',
        'user',
        'followers',
        'is_follower',
        'users',
        'date',
        'messages',
        'last_message',
        'title',
        'title_class',
        'likes_count',
        'is_liked'
    ];

    //get single model

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    //get multiple model

    public function likes(){
        return $this->morphToMany(Like::class, 'likeble');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function getDateAttribute()
    {
        if (is_object($this->last_message)) {
            return HtmlHelper::getDate($this->created_at);

        } else {
            return $this->created_at;
        }
    }

    public function getFollowersAttribute()
    {
        return Follower::getFollowers($this->id, 'conversation');
    }

    public function getIsFollowerAttribute()
    {
        return Follower::isFollower(Auth::user()->id, 'conversation', $this->id);
    }

    public function getIsLikedAttribute(){
        return Like::isLiked(Auth::user()->id, 'conversation', $this->id);

    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->orderBy('created_at', 'desc')->first();
    }

    public function getLikesCountAttribute(){
        return Like::where('post_id', $this->id)->where('type', 'conversation')->count();
    }

    public function getMessagesAttribute()
    {
        return $this->messages()->orderBy('created_at', 'desc')->get();
    }

    public function getTextPreviewAttribute()
    {
        return is_object($this->last_message) ? mb_substr($this->last_message->text, 0, 400) : '';
    }

    public function getTitleAttribute($title){
        $conversation_title = $title ?: 'Без темы';

        if ($this->task) {
            if ($this->task->title) {
                $conversation_title = 'Обсуждение задачи "' . $this->task->title . '"';
            } else {
                $conversation_title = 'Обсуждение задачи "Без названия"';
            }
        }

        return $conversation_title;
    }

    public function getTitleClassAttribute(){
        return $this->title ? 'with-title' : 'without-title';
    }

    public function getUserAttribute()
    {
        return is_object($this->last_message) ? User::find($this->last_message->author_id) : User::find(Auth::user()->id);
    }

    public function getUsersAttribute()
    {
        return User::where('is_active', 1)->get();
    }
}