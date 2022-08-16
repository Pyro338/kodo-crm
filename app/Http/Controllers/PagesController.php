<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Like;
use App\Models\Tag;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Workspace         $workspace
     * @param Conversation|null $conversation
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function conversations(Workspace $workspace, Conversation $conversation = null)
    {
        return view(
            'tasks.conversations',
            [
                'conversation'      => $conversation,
                'conversations'     => $workspace->conversations,
                'current_task'      => $conversation ? $conversation->task : null,
                'current_user'      => User::find(Auth::user()->id),
                'current_workspace' => $workspace,
                'filter_type'       => null,
                'page_title'        => $conversation ? $conversation->title : 'Обсуждения',
                'projects'          => Project::where('status', 1)->get(),
                'search_query'      => null,
                'tags'              => Tag::where('is_visible', 1)->get(),
                'task_id'           => is_object($conversation) && is_object($conversation->task) ? $conversation->task->id : 0,
                'users'             => User::where('is_active', 1)->get()
            ]
        );
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function favorites()
    {
        $current_user = User::find(Auth::user()->id);

        return view(
            'tasks.favorites',
            [
                'current_task'           => null,
                'current_user'           => $current_user,
                'current_workspace'      => Workspace::find($current_user->workspace_id),
                'favorite_comments'      => Like::getLikedComments($current_user),
                'favorite_conversations' => Like::getLikedConversations($current_user),
                'favorite_files'         => Like::getLikedFiles($current_user),
                'favorite_messages'      => Like::getLikedMessages($current_user),
                'favorite_projects'      => Like::getLikedProjects($current_user),
                'favorite_tasks'         => Like::getLikedTasks($current_user),
                'filter_type'            => null,
                'page_title'             => 'Избранное',
                'projects'               => Project::where('status', 1)->get(),
                'search_query'           => null,
                'task_id'                => 0,
                'users'                  => User::where('is_active', 1)->get()
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function inbox(Request $request)
    {
        Auth::user()->unread_messages = 0;
        Auth::user()->save();
        $current_user     = User::find(Auth::user()->id);
        $active_comments  = Comment::getComments($current_user->id, 0);
        $archive_comments = Comment::getComments($current_user->id, 1);

        if ($request->ajax()) {
            switch ($request->tab) {
                case 'active':
                    return view(
                        'tasks.partials.inbox_active_paginate',
                        [
                            'active_comments' => $active_comments,
                        ]
                    );
                    break;
                case 'archive':
                    return view(
                        'tasks.partials.inbox_archive_paginate',
                        [
                            'archive_comments' => $archive_comments,
                        ]
                    );
                    break;
            }
        }

        return view(
            'tasks.inbox',
            [
                'active_comments'   => $active_comments,
                'archive_comments'  => $archive_comments,
                'current_task'      => null,
                'current_user'      => $current_user,
                'current_workspace' => Workspace::find($current_user->workspace_id),
                'filter_type'       => null,
                'page_title'        => 'Входящие',
                'projects'          => Project::where('status', 1)->get(),
                'search_query'      => null,
                'task_id'           => 0,
                'user_id'           => $current_user->id,
                'users'             => User::where('is_active', 1)->get()
            ]
        );
    }

    public function personal()
    {
        $current_user = User::find(Auth::user()->id);

        return view(
            'users.personal',
            [
                'current_user'      => $current_user,
                'current_workspace' => Workspace::find($current_user->workspace_id),
                'filter_type'       => null,
                'page_title'        => 'Личный кабинет',
                'projects'          => Project::where('status', 1)->get(),
                'search_query'      => null,
                'user'              => Auth::user(),
                'users'             => User::where('is_active', 1)->get()
            ]
        );
    }

    /**
     * @param Workspace $workspace
     * @param           $filter_type
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function projects(Workspace $workspace, $filter_type, Request $request)
    {
        $projects_to_main = Project::getProjectsToMain($filter_type);

        if ($request->ajax()) {
            return view(
                'tasks.partials.projects_list',
                [
                    'current_workspace' => $workspace,
                    'filter_type'       => $filter_type,
                    'projects_to_main'  => $projects_to_main
                ]
            );
        }

        return view(
            'tasks.projects',
            [
                'current_user'      => User::find(Auth::user()->id),
                'current_workspace' => $workspace,
                'filter_type'       => $filter_type,
                'page_title'        => $filter_type == 1 ? 'Удаленные проекты' : 'Проекты',
                'projects'          => Project::where('status', 1)->get(),
                'projects_to_main'  => $projects_to_main,
                'search_query'      => null,
                'users'             => User::where('is_active', 1)->get(),
                'workspace_id'      => $workspace->id
            ]
        );
    }

    public function search(Request $request)
    {
        $search_query = $request->q;
        $tasks        = Task::getSearchedTasks($search_query);

        return view(
            'tasks.list',
            [
                'current_task'      => null,
                'current_user'      => User::find(Auth::user()->id),
                'current_workspace' => Workspace::find(User::find(Auth::user()->id)->workspace_id),
                'files'             => File::orderBy('created_at', 'desc')->get(),
                'filter_type'       => 5,
                'followers'         => [],
                'implementer_id'    => null,
                'implementer_name'  => null,
                'is_follower'       => null,
                'page_title'        => 'Результаты поиска: "' . $search_query . '"',
                'project'           => null,
                'projects'          => Project::where('status', 1)->get(),
                'search_query'      => $search_query,
                'task'              => null,
                'task_id'           => 0,
                'tasks'             => $tasks,
                'tasks_body_class'  => 'reports-all-tasks-body',
                'user_id'           => Auth::user()->id,
                'users'             => User::where('is_active', 1)->get(),
            ]
        );
    }

    public function tasksList($workspace_id, $user_id, $project_id, $filter_type, $post_id = 0)
    {
        $task_id = $filter_type == 6 ? 0 : $post_id;

        return view(
            'tasks.list',
            [
                'current_task'      => Task::getTask($task_id),
                'current_user'      => User::find(Auth::user()->id),
                'current_workspace' => Workspace::find($workspace_id),
                'files'             => File::getVisibleFiles(),
                'filter_type'       => $filter_type,
                'implementer_id'    => $user_id != 0 ? $implementer_id = $user_id : null,
                'implementer_name'  => $user_id != 0 ? User::find($user_id)->name : null,
                'page_title'        => Task::getPageTitle($user_id, $project_id, $filter_type, $post_id),
                'project'           => $project_id != 0 ? $project = Project::find($project_id) : null,
                'projects'          => Project::where('status', 1)->get(),
                'search_query'      => null,
                'tags'              => Tag::where('is_visible', 1)->get(),
                'task_id'           => $task_id,
                'tasks'             => Task::getTasks($workspace_id, $user_id, $project_id, $filter_type, $post_id),
                'tasks_body_class'  => Task::getTasksBodyClass($filter_type),
                'user_id'           => $user_id,
                'users'             => User::where('is_active', 1)->get()
            ]
        );
    }

    public function welcome()
    {
        $current_user = User::find(Auth::user()->id);
        if (!$current_user->workspace_id) {
            $current_user->workspace_id = 0;
        }

        return view(
            'welcome',
            [
                'current_user' => $current_user
            ]
        );
    }
}