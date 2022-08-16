<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 10.04.2018
 * Time: 9:17
 */

namespace App\Models;

use App\Helpers\HtmlHelper;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use sngrl\SphinxSearch\SphinxSearch;

class Task extends Model
{
    protected $fillable = [
        'additional_users',
        'delegated_id',
        'due_date',
        'implementer_id',
        'is_private',
        'is_visible',
        'owner_id',
        'parent_id',
        'project_id',
        'status',
        'title',
        'text',
        'time_mark',
        'workspace_id'
    ];

    protected $appends = [
        'files',
        'followers',
        'is_liked',
        'likes_count',
        'mark_class',
        'parent_task',
        'user',
        'used_tags_list'
    ];

    protected $with = [
        'subtasks',
    ];


    //get single model

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'implementer_id');
    }

    //get multiple model

    public function comments()
    {
        return $this->hasMany(Comment::class, 'task_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'task_id', 'id');
    }

    public function likes()
    {
        return $this->morphToMany(Like::class, 'likeble');
    }

    public function subtasks()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tags_tasks', 'task_id', 'tag_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'workspace_id', 'workspace_id');
    }

    //get attributes

    public function getDueDateAttribute($due_date)
    {
        return $due_date ? date('d.m.Y', strtotime($due_date)) : $due_date;
    }

    public function getFilesAttribute()
    {
        return $this->files()->where('is_visible', 1)->orderBy('created_at', 'desc')->get();
    }

    public function getFollowersAttribute()
    {
        return Follower::getFollowers($this->id, 'task');
    }

    public function getImplementerIdAttribute($implementer_id)
    {
        return $this->delegated_id ?: $implementer_id;
    }

    public function getIsLikedAttribute()
    {
        return Like::isLiked(Auth::user()->id, 'task', $this->id);
    }

    public function getLikesCountAttribute()
    {
        return Like::where('post_id', $this->id)->where('type', 'task')->count();
    }

    public function getMarkClassAttribute()
    {
        return $this->time_mark ? 'time-mark-' . $this->time_mark : 'time-mark-new';
    }

    public function getParentTaskAttribute()
    {
        return DB::table('tasks')
            ->where('id', $this->parent_id)
            ->where('is_visible', 1)
            ->first();
    }

    public function getUsedTagsListAttribute()
    {
        $used_tags_list = [];
        foreach ($this->tags()->get() as $key => $tag) {
            if ($tag) {
                $used_tags_list[$key] = $tag->id;
            }
        }

        return $used_tags_list;
    }

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    //

    public static function getSearchedTasks($search_query)
    {
        $sphinx         = new SphinxSearch();
        $search_results = $sphinx->search($search_query, 'crm')
            ->setFieldWeights(
                array(
                    'title' => 10,
                    'text'  => 8
                )
            )
            ->get();
        $tasks = [];
        foreach ($search_results as $key => $search_result) {
            $tasks[$key] = Task::find($search_result->id);
        }

        return $tasks;
    }

    public static function getTasksBodyClass($filter_type)
    {
        switch ($filter_type){
            case 0:
                $tasks_body_class = 'all-tasks-body';
                break;
            case 1:
                $tasks_body_class = 'project-all-tasks-body';
                break;
            default:
                $tasks_body_class  = 'reports-all-tasks-body';
                break;
        }

        return $tasks_body_class;
    }

    public static function getTasks($workspace_id, $user_id, $project_id, $filter_type, $post_id = 0)
    {
        switch ($filter_type) {
            case 0:
                $tasks            = Task::where('is_visible', 1)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->where('delegated_id', $user_id)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
            case 1:
                $tasks            = Task::where('is_visible', 1)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->where('project_id', $project_id)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
            case 2:
                $tasks      = Task::where('is_visible', 1)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->where('owner_id', $user_id)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
            case 3:
                $tasks      = Task::where('is_visible', 1)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->where('implementer_id', $user_id)
                    ->where('delegated_id', '!=', $user_id)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
            case 4:
                $tasks      = Task::where('is_visible', 0)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->where('implementer_id', '!=', 0)
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;
            case 5:
                $recently_date = date('Y-m-d', time() - 86400 * 3);
                $tasks         = Task::where('is_visible', 1)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->where('status', '3')
                    ->where('updated_at', '>', $recently_date)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
            case 6:
                $tag        = Tag::find($post_id);
                $tasks      = $tag->tasks()
                    ->where('workspace_id', $workspace_id)
                    ->where('is_visible', 1)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
            default:
                $tasks   = Task::where('is_visible', 1)
                    ->where('workspace_id', $workspace_id)
                    ->where('parent_id', null)
                    ->orderBy('order', 'asc')
                    ->get();
                break;
        }

        return $tasks;
    }

    public static function getPageTitle($user_id, $project_id, $filter_type, $post_id = 0)
    {
        if (is_object(User::find($user_id))) {
            $implementer_name = ($user_id != 0) ? User::find($user_id)->name : null;
            $implementer_id   = ($user_id != 0) ? $implementer_id = $user_id : null;
        } else {
            $implementer_name = null;
            $implementer_id   = null;
        }

        switch ($filter_type) {
            case 0:
                $page_title       = ($implementer_id == Auth::user()->id) ? 'Мои задачи' : 'Задачи пользователя ' . $implementer_name;
                break;
            case 1:
                $project = Project::find($project_id);
                $page_title       = 'Проект "' . $project->title . '"';
                break;
            case 2:
                $page_title = ($implementer_id == Auth::user()->id) ? 'Задачи, созданные мной' : 'Задачи, созданные пользователем ' .
                    $implementer_name;
                break;
            case 3:
                $page_title = 'Переданные задачи';
                break;
            case 4:
                $page_title = 'Удаленные задачи';
                break;
            case 5:
                $page_title    = 'Недавно завершенные задачи';
                break;
            case 6:
                $tag        = Tag::find($post_id);
                $page_title = 'Задачи с меткой: "' . $tag->title . '"';
                break;
            default:
                $page_title        = "Задачи";
                break;
        }

        return $page_title;
    }

    public static function getTask($task_id)
    {
        if ($current_task = Task::find($task_id)) {
            $users            = User::where('is_active', 1)->get();
            foreach ($users as $user) {
                $user->followed_task = Follower::isFollower($user->id, 'task', $task_id);
            }

            foreach ($current_task->comments as $comment) {
                $comment->text   = HtmlHelper::findLinks($comment->text);
            }

            $current_task->followers = Follower::getFollowers($task_id, 'task');
        }

        return $current_task ? $current_task : null;
    }

    public static function createEmpty($request)
    {
        $current_user         = Auth::user()->id;
        $target_user_id       = $request->implementer_id == 0 ? $current_user : $request->implementer_id;
        $status               = $request->status == '4' ? 4 : 1;
        $task                 = new Task;
        $task->owner_id       = $current_user;
        $task->implementer_id = $target_user_id;
        $task->delegated_id   = $target_user_id;
        $task->status         = $status;
        $task->is_visible     = 0;
        $task->time_mark      = 'new';
        if ($request->project_id) {
            $task->project_id = $request->project_id;
        }
        $task->workspace_id = $request->workspace_id;
        if ($request->parent_id) {
            $task->parent_id = $request->parent_id;
        }
        $task->save();

        return $task;
    }
}