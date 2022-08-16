<?php

namespace App\Models;


use Illuminate\Support\Facades\Auth;

class File extends Model
{
    protected $fillable = [
        'author',
        'path',
        'is_public',
        'is_visible',
        'type',
        'original_filename',
        'task_id'
    ];

    protected $appends = [
        'class',
        'is_liked',
        'likes_count',
        'alt'
    ];

    //get single model

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function user()
    {
        return $this->hasOne(Conversation::class, 'id', 'author');
    }

    //get multiple model

    public function likes(){
        return $this->morphToMany(Like::class, 'likeble');
    }

    //get attributes

    public function getLikesCountAttribute()
    {
        return Like::where('post_id', $this->id)->where('type', 'file')->count();
    }

    public function getIsLikedAttribute()
    {
        return Like::isLiked(Auth::user()->id, 'file', $this->id);
    }

    public function getClassAttribute()
    {
        return file_exists(storage_path('app/' . $this->path)) ? 'img-small-preview' : 'img-without-picture';
    }

    public function getAltAttribute()
    {
        return file_exists(storage_path('app/' . $this->path)) ? $this->original_filename : 'Изображение не найдено';
    }

    //

    public static function getVisibleFiles()
    {
        return File::where('is_visible', 1)->orderBy('created_at', 'desc')->get();
    }

    public static function createFile($request, $attachment)
    {
        $new_file = new File;
        $new_file->author            = Auth::user()->id;
        $new_file->original_filename = $attachment->getClientOriginalName();
        $new_file->type              = $attachment->getClientOriginalExtension();
        $new_file->path              = $attachment->store('uploads/' . Auth::user()->id);
        $new_file->task_id           = $request->task_id;
        $new_file->workspace_id      = $request->workspace_id;
        $new_file->save();

        return $new_file;
    }

    public static function addFileToTask($file_id, $task)
    {
        if ($file = File::find((int)$file_id)) {
            $file->task_id      = $task->id;
            $file->workspace_id = $task->workspace_id;
            $file->is_public    = 1;
            $file->save();
        }
    }
}
