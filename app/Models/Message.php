<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 25.05.2018
 * Time: 13:26
 */

namespace App\Models;

use App\Helpers\HtmlHelper;

use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    protected $fillable = [
        'text',
        'conversation_id',
        'is_visible',
        'attachment_id',
        'author_id'
    ];

    protected $appends = [
        'author',
        'class',
        'is_liked',
        'likes_count',
        'conversation_title',
    ];

    //get single model

    public function attachment(){
        return $this->hasOne(File::class, 'id', 'attachment_id');
    }

    public function conversation(){
        return $this->hasOne(Conversation::class, 'id', 'conversation_id');
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'author_id');
    }

    //get multiple model

    public function likes(){
        return $this->morphToMany(Like::class, 'likeble');
    }

    //get attributes

    public function getAuthorAttribute(){
        return $this->user->name;
    }

    public function getClassAttribute(){
        return ($this->author_id == Auth::user()->id) ? 'message-item-my' : 'message-item-other';
    }

    public function getConversationTitleAttribute(){
        return $this->conversation->title ? $this->conversation->title : 'Без темы';
    }

    public function getIsLikedAttribute(){
        return Like::isLiked(Auth::user()->id, 'message', $this->id);
    }

    public function getLikesCountAttribute(){
        return Like::where('post_id', $this->id)->where('type', 'message')->count();
    }

    public function getTextAttribute($text){
        return HtmlHelper::findLinks($text);
    }

    public function getTimeAttribute(){
        return date('d.m.Y h:i', strtotime($this->created_at));
    }

    public function getWorkspaceIdAttribute(){
        return $this->conversation->workspace_id;
    }

    public static function createMessage($text, $author_id, $attachment_id, $conversation_id)
    {
        $message                  = new Message;
        $message->text            = $text;
        $message->author_id       = $author_id;
        $message->attachment_id   = $attachment_id;
        $message->conversation_id = $conversation_id;
        $message->save();
        $message->author = User::find($message->author_id)->name;
        if ($message->attachment_id) {
            $message->attachment = File::find($message->attachment_id);
        }

        return $message;
    }
}