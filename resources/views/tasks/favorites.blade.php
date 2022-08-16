@extends('layouts.crm')

@section('content')
    @can('editTasks')
        <div class="container-fluid text-center page-header">
            <h1>{{$page_title}}</h1>
            <span class="page-header-submenu page-header-submenu-active" id="favorite-files-button">Файлы</span>
            <span class="page-header-submenu" id="favorite-projects-button">Проекты</span>
            <span class="page-header-submenu" id="favorite-tasks-button">Задачи</span>
            <span class="page-header-submenu" id="favorite-comments-button">Комментарии</span>
            <span class="page-header-submenu" id="favorite-conversations-button">Обсуждения</span>
            <span class="page-header-submenu" id="favorite-messages-button">Сообщения</span>
        </div>
        <div class="container-fluid page-body">
            <div class="row">
                <div class="col-md-12 favorites-panel" id="favorite-files">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="favorite-files-block">
                                @forelse($favorite_files as $favorite_file)
                                    <div class="img-tile-item">
                                        <div class="img-title-header">
                                            <a href="{{route('download', $favorite_file->id)}}"
                                               class="crm-button"
                                               style="margin-right: 0;"
                                            >
                                                <i class="fa fa-download" aria-hidden="true"></i>
                                            </a>
                                            <div class="file-like-button float-right crm-button like-button"
                                                 id="file-like-button-{{$favorite_file->id}}"
                                                 data-id="{{$favorite_file->id}}"
                                                 data-type="file"
                                                 title="{{$favorite_file->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                                            >
                                                <input type="hidden" id="file-likes-count-input-{{$favorite_file->id}}" value="{{$favorite_file->likes_count}}">
                                                <i class="fa {{$favorite_file->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="file-likes-count-{{$favorite_file->id}}">{{$favorite_file->likes_count}}</span>
                                            </div>
                                        </div>
                                        <div class="img-tile"
                                             style="background-image: url('{{route('download', $favorite_file->id)}}')"
                                             data-url="{{route('download', $favorite_file->id)}}"
                                        >
                                        </div>
                                    </div>
                                @empty
                                    <p>Нет избранных файлов</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 favorites-panel" id="favorite-projects" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @forelse($favorite_projects as $favorite_project)
                                <div class="favorite-project-item favorite-item task-item">
                                    <div class="favorite-project-header">
                                        <b>Проект: </b>
                                        <a href="{{route('list', [
                                                'workspace_id' => $favorite_project->workspace_id,
                                                'user_id'      => 0,
                                                'project_id'   => $favorite_project->id,
                                                'filter_type'  => 1
                                            ])}}"
                                        >
                                            <b class="favorite-project-alias">{{$favorite_project->alias}}</b>
                                            <span class="favorite-project-title">{{$favorite_project->title}}</span>
                                        </a>
                                        <div class="project-like-button float-right crm-button like-button"
                                             id="project-like-button-{{$favorite_project->id}}"
                                             data-id="{{$favorite_project->id}}"
                                             data-type="project"
                                             title="{{$favorite_project->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                                        >
                                            <input type="hidden" id="project-likes-count-input-{{$favorite_project->id}}" value="{{$favorite_project->likes_count}}">
                                            <i class="fa {{$favorite_project->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="project-likes-count-{{$favorite_project->id}}">{{$favorite_project->likes->count()}}</span>
                                        </div>
                                    </div>
                                    <div class="favorite-project-body favorite-body">
                                        {!! $favorite_project->text !!}
                                    </div>
                                </div>
                            @empty
                                <p>Нет избранных комментариев</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-12 favorites-panel" id="favorite-tasks" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @forelse($favorite_tasks as $favorite_task)
                                <div class="favorite-task-item favorite-item task-item">
                                    <div class="favorite-task-header">
                                        <b>Задача: </b>
                                        <a href="{{route('list', [
                                                'workspace_id' => $favorite_task->workspace_id,
                                                'user_id'      => $favorite_task->delegated_id,
                                                'project_id'   => 0,
                                                'filter_type'  => 0,
                                                'task_id'      => $favorite_task->id
                                            ])}}"
                                        >
                                            <b class="favorite-task-alias">{{is_object($favorite_task->project) ? $favorite_task->project->alias : ''}}</b>
                                            <span class="favorite-task-title">{{$favorite_task->title}}</span>
                                        </a>
                                        <div class="task-like-button float-right crm-button like-button"
                                             id="task-like-button-{{$favorite_task->id}}"
                                             data-id="{{$favorite_task->id}}"
                                             data-type="task"
                                             title="{{$favorite_task->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                                        >
                                            <input type="hidden" id="task-likes-count-input-{{$favorite_task->id}}" value="{{$favorite_task->likes_count}}">
                                            <i class="fa {{$favorite_task->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="task-likes-count-{{$favorite_task->id}}">{{$favorite_task->likes_count}}</span>
                                        </div>
                                        @if(is_object($favorite_task->project))
                                            <div class="float-right project-title">
                                                <a href="{{route('list', [
                                                        'workspace_id' => $favorite_task->project->workspace_id,
                                                        'user_id'      => 0,
                                                        'project_id'   => $favorite_task->project->id,
                                                        'filter_type'  => 1,
                                                    ])}}"
                                                   style="background-color: {{$favorite_task->project->color}}"
                                                >
                                                    <span class="favorite-task-project">{{$favorite_task->project->title}}</span>
                                                </a>
                                            </div>
                                        @endif
                                        <a href="{{route('list', [
                                            'workspace_id' => $favorite_task->workspace_id,
                                            'user_id' => $favorite_task->delegated_id,
                                            'project_id' => 0,
                                            'filter_type' => 0
                                            ])}}"
                                           class="users-item users-item-small float-right"
                                           style="background-color: {{$favorite_task->user->color}};
                                                  background-image: {{$favorite_task->user->background_image}};
                                                  margin-top: 2px;
                                                 "
                                           data-user_fullname="{{$favorite_task->user->fullname}}"
                                           data-user_department="{{$favorite_task->user->department}}"
                                           data-user_office="{{$favorite_task->user->office}}"
                                        >
                                            {{$favorite_task->user->first_letters}}
                                        </a>
                                    </div>
                                    <div class="favorite-task-body favorite-body">
                                        {!! $favorite_task->text !!}
                                    </div>
                                </div>
                            @empty
                                <p>Нет избранных задач</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-12 favorites-panel" id="favorite-comments" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @forelse($favorite_comments as $favorite_comment)
                                <div class="favorite-comment-item favorite-item task-item">
                                    <div class="favorite-comment-header">
                                        <b>Комментарий к задаче: </b>
                                        <a href="{{route('list', [
                                                'workspace_id' => $favorite_comment->task->workspace_id,
                                                'user_id'      => $favorite_comment->task->delegated_id,
                                                'project_id'   => 0,
                                                'filter_type'  => 0,
                                                'task_id'      => $favorite_comment->task_id
                                            ])}}"
                                        >
                                            <b class="favorite-comment-alias">{{$favorite_comment->project_id ? $favorite_comment->alias : ''}}</b>
                                            <span class="favorite-comment-title">{{$favorite_comment->task->title}}</span>
                                        </a>
                                        <div class="comment-like-button float-right crm-button like-button"
                                             id="comment-like-button-{{$favorite_comment->id}}"
                                             data-id="{{$favorite_comment->id}}"
                                             data-type="comment"
                                             title="{{$favorite_comment->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                                        >
                                            <input type="hidden" id="comment-likes-count-input-{{$favorite_comment->id}}" value="{{$favorite_comment->likes_count}}">
                                            <i class="fa {{$favorite_comment->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="comment-likes-count-{{$favorite_comment->id}}">{{$favorite_comment->likes_count}}</span>
                                        </div>
                                        @if($favorite_comment->project_id)
                                            <div class="float-right project-title">
                                                <a href="{{route('list', [
                                                        'workspace_id' => $favorite_comment->task->workspace_id,
                                                        'user_id'      => 0,
                                                        'project_id'   => $favorite_comment->project_id,
                                                        'filter_type'  => 1,
                                                    ])}}"
                                                   style="background-color: {{$favorite_comment->project_color}}"
                                                >
                                                    <span class="favorite-comment-project">{{$favorite_comment->project_name}}</span>
                                                </a>
                                            </div>
                                        @endif
                                        @if($favorite_comment->type == 'comment')
                                            <a href="{{route('list', [
                                                'workspace_id' => $favorite_comment->task->workspace_id,
                                                'user_id' => $favorite_comment->task->delegated_id,
                                                'project_id' => 0,
                                                'filter_type' => 0
                                                ])}}"
                                               class="users-item users-item-small float-right"
                                               style="background-color: {{$favorite_comment->author->color}};
                                                       background-image: {{$favorite_comment->author->background_image}};
                                                       margin-top: 2px;
                                                       "
                                               data-user_fullname="{{$favorite_comment->author->fullname}}"
                                               data-user_department="{{$favorite_comment->author->department}}"
                                               data-user_office="{{$favorite_comment->author->office}}"
                                            >
                                                {{$favorite_comment->author->first_letters}}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="favorite-comment-body favorite-body">
                                        {!! $favorite_comment->text !!}
                                    </div>
                                </div>
                            @empty
                                <p>Нет избранных комментариев</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-12 favorites-panel" id="favorite-conversations" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @forelse($favorite_conversations as $favorite_conversation)
                                <div class="favorite-conversation-item favorite-item task-item">
                                    <div class="favorite-conversation-header">
                                        <b>Обсуждение: </b>
                                        <a href="{{route('conversations', [
                                                'workspace_id' => $favorite_conversation->workspace_id,
                                                'conversation_id'      => $favorite_conversation->id,
                                            ])}}"
                                        >
                                            <span class="favorite-conversation-title">{{$favorite_conversation->title}}</span>
                                        </a>
                                        <div class="conversation-like-button float-right crm-button like-button"
                                             id="conversation-like-button-{{$favorite_conversation->id}}"
                                             data-id="{{$favorite_conversation->id}}"
                                             data-type="conversation"
                                             title="{{$favorite_conversation->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                                        >
                                            <input type="hidden" id="conversation-likes-count-input-{{$favorite_conversation->id}}" value="{{$favorite_conversation->likes_count}}">
                                            <i class="fa {{$favorite_conversation->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="conversation-likes-count-{{$favorite_conversation->id}}">{{$favorite_conversation->likes_count}}</span>
                                        </div>
                                    </div>
                                    <div class="favorite-conversation-body favorite-body">
                                        {!! $favorite_conversation->text_preview !!}
                                    </div>
                                </div>
                            @empty
                                <p>Нет избранных обсуждений</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-12 favorites-panel" id="favorite-messages" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @forelse($favorite_messages as $favorite_message)
                                <div class="favorite-message-item favorite-item task-item">
                                    <div class="favorite-message-header">
                                        <b>Сообщение в обсуждении: </b>
                                        <a href="{{route('conversations', [
                                                'workspace_id' => $favorite_message->workspace_id,
                                                'conversation_id'      => $favorite_message->conversation_id,
                                            ])}}"
                                        >
                                            <span class="favorite-conversation-title">{{$favorite_message->conversation_title}}</span>
                                        </a>
                                        <div class="message-like-button float-right crm-button like-button"
                                             id="message-like-button-{{$favorite_message->id}}"
                                             data-id="{{$favorite_message->id}}"
                                             data-type="message"
                                             title="{{$favorite_message->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                                        >
                                            <input type="hidden" id="message-likes-count-input-{{$favorite_message->id}}" value="{{$favorite_message->likes_count}}">
                                            <i class="fa {{$favorite_message->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="message-likes-count-{{$favorite_message->id}}">{{$favorite_message->likes_count}}</span>
                                        </div>
                                    </div>
                                    <div class="favorite-message-body favorite-body">
                                        {!! $favorite_message->text !!}
                                    </div>
                                </div>
                            @empty
                                <p>Нет избранных обсуждений</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            У вас нет прав для просмотра данной страницы
        </div>
    @endcan
    <script src="{{asset('js/pages_scripts/favorites.js')}}"></script>
@endsection