<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 06.08.2018
 * Time: 10:21
 */

namespace App\Models;

use App\Helpers\HtmlHelper;


class Tag extends Model
{
    protected $fillable = [
        'title', 'is_visible'
    ];

    protected $appends = [
      'color'
    ];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'tags_tasks', 'tag_id', 'task_id');
    }

    public function getColorAttribute(){
        return HtmlHelper::userColor($this->title);
    }
}