<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 10.04.2018
 * Time: 11:20
 */

namespace App\Models;


use App\Helpers\HtmlHelper;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    protected $fillable = [
        'owner_id',
        'status',
        'title',
        'text',
        'alias',
        'workspace_id'
    ];

    protected $appends = [
        'color',
        'likes_count',
        'is_liked'
    ];

    //get multiple model

    public function likes()
    {
        return $this->morphToMany(Like::class, 'likeble');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    //get attributes

    public function getColorAttribute()
    {
        return HtmlHelper::userColor($this->title);
    }

    public function getLikesCountAttribute()
    {
        return Like::where('post_id', $this->id)->where('type', 'project')->count();
    }

    public function getIsLikedAttribute()
    {
        return Like::isLiked(Auth::user()->id, 'project', $this->id);
    }

    public function getTextAttribute($text){
        return HtmlHelper::findLinks($text);
    }

    //

    public static function getProjectsToMain($filter_type){
        if ($filter_type == 1){
            $projects =  Project::where('status', 0)->orderBy('updated_at', 'desc')->paginate(5);
        }else{
            $projects =  Project::where('status', 1)->orderBy('updated_at', 'desc')->paginate(5);
        }

        foreach ($projects as $project) {
            $project->all_tasks_count        = Project::getAllTasksCount($project);
            $project->complete_tasks_count   = Project::getCompleteTasksCount($project);
            $project->incomplete_tasks_count = Project::getIncompleteTasksCount($project);
            $project->complete_percent = $project->all_tasks_count > 0 ? Project::getCompletePercent($project) : 0;
            $project->text = HtmlHelper::findLinks($project->text);
            if ($project->workspace_id) {
                $project->workspace = Workspace::find($project->workspace_id);
            }
            $project->likes_count = $project->likes->count();
            $project->is_liked    = Like::isLiked(Auth::user()->id, 'project', $project->id);
        }

        return $projects;
    }

    public static function getAllTasksCount($project)
    {
        return $project->tasks->where('is_visible', 1)->count();
    }

    public static function getCompleteTasksCount($project)
    {
        return $project->tasks->where('is_visible', 1)->where('status', 3)->count();
    }

    public static function getIncompleteTasksCount($project)
    {
        return Project::getAllTasksCount($project) - Project::getCompleteTasksCount($project);
    }

    public static function getCompletePercent($project)
    {
        return round(100 / Project::getAllTasksCount($project) * Project::getCompleteTasksCount($project));
    }
}