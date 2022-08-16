<?php

namespace App\Http\Controllers\Api;

use App\Classes\Transformers\ModelTransformer;
use App\Models\File;
use App\Models\Tag;
use App\Models\User;
use App\Models\Task;
use App\Models\Comment;
use App\Models\Follower;
use App\Services\PusherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class TaskController extends Controller
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
        $task = Task::create($request->all());
        if ($request->due_date) {
            $task->due_date = date('Y-m-d', strtotime($request->due_date));
            $task->save();
        }
        $task->user  = User::find($task->delegated_id);
        $task->users = User::all();
        if (!Follower::isFollower($task->owner_id, 'task', $task->id)) {
            Follower::createFollower(
                [
                    'user_id' => $task->owner_id,
                    'type'    => 'task',
                    'post_id' => $task->id
                ]
            );
        }
        if (!Follower::isFollower($task->delegated_id, 'task', $task->id)) {
            Follower::createFollower(
                [
                    'user_id' => $task->delegated_id,
                    'type'    => 'task',
                    'post_id' => $task->id
                ]
            );
        }
        if ($attachment_ids = $request->attachment_ids) {
            $attachment_ids = explode(",", $attachment_ids);
            foreach ($attachment_ids as $attachment_id) {
                File::addFileToTask($attachment_id, $task);
            }
        }

        if ($task) {
            return
                [
                    'success'     => $task,
                    'attachments' => $attachment_ids
                ];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Task $task
     *
     * @return array
     */
    public function show(Task $task)
    {
        if ($task->delegated_id) {
            $implementer_name = User::where('id', $task->delegated_id)->first()->name;
            $implementer_id   = $task->delegated_id;
        } else {
            $implementer_name = User::where('id', $task->implementer_id)->first()->name;
            $implementer_id   = $task->implementer_id;
        }
        $result['task']             = ModelTransformer::transformTask($task);
        $result['all_tags']         = Tag::where('is_visible', 1)->get();
        $result['comments']         = Comment::where('task_id', $task->id)->where('is_visible', 1)->where('recipient_id', 0)->get();
        $result['implementer_name'] = $implementer_name;
        $result['implementer_id']   = $implementer_id;
        $result['files']            = $task->files;
        $result['users']            = User::where('is_active', 1)->get();
        $result['followers']        = Follower::getFollowers($task->id, 'task');
        $result['is_follower']      = Follower::isFollower(Auth::user()->id, 'task', $task->id);

        if ($result) {
            return ['success' => $result];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Task                     $task
     *
     * @return array
     */
    public function update(Task $task, Request $request)
    {
        $task->fill($request->all());
        $task->project_id = $request->project_id == '0' ? null : $request->project_id;
        if ($request->due_date) {
            $task->due_date = date('Y-m-d', strtotime($request->due_date));
        } else {
            $task->due_date = null;
        }
        if (!Follower::isFollower($task->delegated_id, 'task', $task->id)) {
            Follower::createFollower(
                [
                    'type'    => 'task',
                    'post_id' => $task->id,
                    'user_id' => $task->delegated_id
                ]
            );
        }
        if ($task->parent_id) {
            $parent_followers = Follower::getFollowers($task->parent_id, 'task');
            foreach ($parent_followers as $parent_follower) {
                if (!Follower::isFollower($parent_follower->id, 'task', $task->id)) {
                    Follower::createFollower(
                        [
                            'type'    => 'task',
                            'post_id' => $task->id,
                            'user_id' => $parent_follower->id
                        ]
                    );
                }
            }
        }
        PusherService::pushTaskMentionData($request, $task);
        $followers = Follower::getFollowers($task->id, 'task');
        foreach ($followers as $follower) {
            Follower::taskUpdated($follower, $task);
        }
        $task->is_visible = 1;
        $task->save();
        $task              = ModelTransformer::transformTask($task);
        $task['comment']   = Comment::sendSystemComment($task['id'], 'task_updated', 'Задача была изменена');
        $task['followers'] = Follower::getFollowers($task['id'], 'task');
        $task['users']     = User::where('is_active', 1)->get();

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Task $task
     *
     * @return array
     */
    public function destroy(Task $task)
    {
        $task->is_visible = 0;
        $task->save();
        $comment_text = 'Пользователь ' . Auth::user()->name . ' удалил задачу';
        $followers    = Follower::getFollowers($task->id, 'task');
        foreach ($followers as $follower) {
            Follower::taskDeleted($follower, $comment_text, $task);
        }
        Comment::sendSystemComment($task->id, 'task_deleted', $comment_text);

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function restore(Task $task)
    {
        $task->is_visible = 1;
        $task->save();
        $current_user = User::find(Auth::user()->id);
        $comment_text = 'Пользователь ' . $current_user->link . ' восстановил задачу';
        $followers    = Follower::getFollowers($task->id, 'task');
        foreach ($followers as $follower) {
            Follower::taskRestored($follower, $comment_text, $task);
        }
        Comment::sendSystemComment($task->id, 'task_deleted', $comment_text);

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function createEmpty(Request $request)
    {
        $task = Task::createEmpty($request);
        if (!Follower::isFollower($task->owner_id, 'task', $task->id)) {
            Follower::createFollower(
                [
                    'user_id' => $task->owner_id,
                    'type'    => 'task',
                    'post_id' => $task->id
                ]
            );
        }
        if (!Follower::isFollower($task->delegated_id, 'task', $task->id)) {
            Follower::createFollower(
                [
                    'user_id' => $task->delegated_id,
                    'type'    => 'task',
                    'post_id' => $task->id
                ]
            );
        }

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function saveOrder(Request $request)
    {
        foreach ($request->sort as $i => $row) {
            $id          = intval($row);
            $task        = Task::find($id);
            $task->order = $i;
            $task->save();
            $result[$i] = $task;
        }

        if ($result) {
            return ['success' => $result];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function addTag(Task $task, Request $request)
    {
        $task->tags()->attach($request->tag_id);
        $tag = Tag::find($request->tag_id);
        $tag = ModelTransformer::transformTag($tag);

        if ($tag) {
            return ['success' => $tag];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function removeTag(Task $task, Request $request)
    {
        $task->tags()->detach($request->tag_id);
        $tag = Tag::find($request->tag_id);
        $tag = ModelTransformer::transformTag($tag);

        if ($tag) {
            return ['success' => $tag];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function assign(Task $task, Request $request)
    {
        $task->delegated_id = $request->user_id;
        $task->save();
        $comment_text = 'Пользователь ' . Auth::user()->name . ' передал задачу пользователю ' . User::where('id', $task->delegated_id)->first()->name;
        if (!Follower::isFollower($task->delegated_id, 'task', $task->id)) {
            Follower::createFollower(
                [
                    'user_id' => $task->delegated_id,
                    'type'    => 'task',
                    'post_id' => $task->id
                ]
            );
        }
        $followers = Follower::getFollowers($task->id, 'task');
        foreach ($followers as $follower) {
            Follower::taskDelegated($follower, $comment_text, $task);
        }
        Comment::sendSystemComment($task->id, 'task_delegated', $comment_text);

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function changeTimeMark(Task $task, Request $request)
    {
        $last_order      = Task::all()->max('order');
        $task->time_mark = $request->time_mark;
        $task->order     = $last_order + 1;
        $task->save();

        if ($task) {
            return ['success' => $task->time_mark];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function togglePrivate(Task $task)
    {
        if ($task->is_private != 1) {
            $task->is_private = 1;
        } else {
            $task->is_private = 0;
        }
        $task->save();
        Comment::sendSystemComment($task->id, 'task_updated', 'Задача была изменена');

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function toggleSection(Task $task)
    {
        if ($task->status == 4) {
            $task->status = 1;
        } else {
            $task->status = 4;
        }
        $task->save();
        $task->comment = Comment::sendSystemComment($task->id, 'task_updated', 'Задача была изменена');

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function toggleStatus(Task $task)
    {
        $current_user = User::find(Auth::user()->id);
        switch ($task->status) {
            case 0:
            case 1:
            case 2:
                $task->status = 3;
                $comment_text = 'Пользователь ' . $current_user->link . ' завершил задачу';
                break;
            case 3:
                $task->status = 1;
                $comment_text = 'Пользователь ' . $current_user->link . ' возобновил задачу';
                break;
            case 4:
                $task->status = 5;
                $comment_text = 'Пользователь ' . $current_user->link . ' завершил секцию';
                break;
            case 5:
                $task->status = 4;
                $comment_text = 'Пользователь ' . $current_user->link . ' возобновил секцию';
                break;
        }
        $task->save();
        $followers = Follower::getFollowers($task->id, 'task');
        foreach ($followers as $follower) {
            Follower::taskStatusChanged($follower, $comment_text, $task);
        }
        $task->comment = Comment::sendSystemComment($task->id, 'task_status_changed', $comment_text);

        if ($task) {
            return ['success' => $task];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function getCalendarEvents(Request $request)
    {
        $events = Task::where('is_visible', 1)
            ->where('due_date', '<=', $request->end)
            ->where('due_date', '>=', $request->start)
            ->get();
        switch ($request->filter_type) {
            case 0:
                $events = Task::where('is_visible', 1)
                    ->where('due_date', '<=', $request->end)
                    ->where('due_date', '>=', $request->start)
                    ->where('delegated_id', $request->user_id)
                    ->get();
                break;
            case 1:
                $events = Task::where('is_visible', 1)
                    ->where('due_date', '<=', $request->end)
                    ->where('due_date', '>=', $request->start)
                    ->where('project_id', $request->project_id)
                    ->get();
                break;
            case 2:
                $events = Task::where('is_visible', 1)
                    ->where('due_date', '<=', $request->end)
                    ->where('due_date', '>=', $request->start)
                    ->where('owner_id', $request->user_id)
                    ->get();
                break;
            case 3:
                $events = Task::where('is_visible', 1)
                    ->where('due_date', '<=', $request->end)
                    ->where('due_date', '>=', $request->start)
                    ->where('implementer_id', $request->user_id)
                    ->where('delegated_id', '!=', $request->user_id)
                    ->get();
                break;
            case 4:
                $events = Task::where('is_visible', 0)
                    ->where('due_date', '<=', $request->end)
                    ->where('due_date', '>=', $request->start)
                    ->where('implementer_id', '!=', 0)
                    ->get();
                break;
            case 5:
                $recently_date = date('Y-m-d', time() - 86400 * 3);
                $events        = Task::where('is_visible', 0)
                    ->where('due_date', '<=', $request->end)
                    ->where('due_date', '>=', $request->start)
                    ->where('status', 3)
                    ->where('updated_at', '>', $recently_date)
                    ->get();
                break;
        }

        foreach ($events as $event) {
            $event->start = $event->end = date('Y-m-d', strtotime($event->due_date));
        }

        return ($events);
    }
}