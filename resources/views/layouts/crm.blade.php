<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | {{$page_title}}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.structure.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/crm-styles.css') }}" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="{{ asset('fc/fullcalendar.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('fc/lib/moment.min.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js')}}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/datepicker-ru.js') }}"></script>
    <script src="{{ asset('js/autosize.js') }}"></script>
    <script src="{{ asset('js/main.js')}}"></script>
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/autobahn.js') }}"></script>
    <script src="{{ asset('fc/fullcalendar.js') }}"></script>
    <script src="{{ asset('fc/lang-all.js') }}"></script>

    <!-- Main Quill library -->
    <script src="//cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <!-- Theme included stylesheets -->
    <link href="//cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="//cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">

    <!--quill emojis-->
    <link rel="stylesheet" type="text/css" href="{{asset('qe/quill-emoji.css')}}">
    <script src="{{asset('qe/quill-emoji.js')}}"></script>

    <!--quill mention-->
    <link rel="stylesheet" type="text/css" href="{{asset('qm/quill.mention.min.css')}}">
    <script src="{{asset('qm/quill.mention.min.js')}}"></script>

    <!--dropzone-->
    <link href="{{ asset('css/dropzone.css') }}" rel="stylesheet">
    <script src="{{asset('js/dropzone.js')}}"></script>
</head>
<body>
<input type="hidden" id="ip" value="{{isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1'}}">
<input type="hidden" id="filter-type" value="{{$filter_type}}">
<input type="hidden" id="enable-sound" value="{{Auth::user()->enable_sound}}">
<input type="hidden" id="current-user" value="{{$current_user->id}}">
<input type="hidden" id="current-workspace" value="{{$current_user->workspace_id}}">
<input type="hidden" id="unread-messages" value="{{$current_user->unread_messages}}">
<audio id="notification-sound">
    <source src="/audio/notification.mp3" type="audio/mp3">
</audio>
<div class="container-fluid crm">
    <div class="row">
        <div class="sidebar">
            <a class="logo js-open-menu-link" href="{{route('welcome')}}"></a>
            <div class="close"><i class="fa fa-times" aria-hidden="true"></i></div>
            <div class="menu-block">
                <div class="menu-header reports-header">
                    <span class="reports-closed"><i class="fa fa-caret-right" aria-hidden="true"></i></span>
                    <span class="reports-opened"><i class="fa fa-caret-down" aria-hidden="true"></i></span>
                    Отчеты
                </div>
                <ul class="menu-body reports-body">
                    <li><a href="{{route('list', [
                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                    'user_id' => $current_user->id,
                    'project_id' => 0,
                    'filter_type' => 2
                    ])}}">Созданные задачи</a></li>
                    <li><a href="{{route('list', [
                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                    'user_id' => $current_user->id,
                    'project_id' => 0,
                    'filter_type' => 3
                    ])}}">Переданные задачи</a></li>
                    <li><a href="{{route('list', [
                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                    'user_id' => 0,
                    'project_id' => 0,
                    'filter_type' => 5
                    ])}}">Недавно завершенные задачи</a></li>
                    <li><a href="{{route('list', [
                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                    'user_id' => 0,
                    'project_id' => 0,
                    'filter_type' => 4
                    ])}}">Удаленные задачи</a></li>
                    <li><a href="{{route('projects', [
                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                    'filter_type' => 1
                    ])}}">Удаленные проекты</a></li>
                </ul>
                <div class="users-body">
                    @foreach($users as $user)
                        @if(is_object($current_workspace) && in_array($current_workspace->id, $user->workspaces_list))
                            <a href="{{route('list', [
                        'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                        'user_id' => $user->id,
                        'project_id' => 0,
                        'filter_type' => 0
                        ])}}"
                               class="users-item"
                               style="background-color: {{$user->color}}; background-image: {{$user->background_image}};"
                               data-user_fullname="{{$user->fullname}}"
                               data-user_department="{{$user->department}}"
                               data-user_office="{{$user->office}}"
                            >
                                {{$user->first_letters}}
                            </a>
                        @endif
                    @endforeach
                </div>
                <div class="menu-header conversations-header">
                    <a class="left-menu-link" href="{{route('conversations', ['workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id])}}">Обсуждения</a>
                    <a href="#" class="new-conversation-button"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>
                </div>
                <div class="menu-header tasks-header">
                    <a  class="left-menu-link"
                        href="{{route('list', [
                            'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                            'user_id' => $current_user->id,
                            'project_id' => 0,
                            'filter_type' => 0
                            ])}}"
                    >
                        Задачи
                    </a>
                    <a href="#" class="new-task-button"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>
                </div>
                <div class="menu-header projects-header">
                    <a class="left-menu-link"
                       href="{{route('projects', [
                        'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                        'filter_type' => 0
                        ])}}"
                    >
                        Проекты
                    </a>
                    <a href="#" class="new-project-button"><i class="fa fa-plus-circle" aria-hidden="true"></i></a>
                </div>
                <div class="menu-body projects-body">
                    @foreach($projects as $project)
                        @if(is_object($current_workspace) && $project->workspace_id == $current_workspace->id
                        || !is_object($current_workspace))
                            @if(in_array($project->workspace_id, $current_user->workspaces_list))
                                <li>
                                    <a href="{{route('list', [
                                'workspace_id' => $project->workspace_id,
                                'user_id' => 0,
                                'project_id' => $project->id,
                                'filter_type' => 1
                                ])}}" id="left-menu-project-{{$project->id}}">
                                        {{$project->title}}
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="main">
            <div class="header row text-center">
                <div class="col-md-2">
                    <a href="#" id="my-tasks-button">
                        <span class="my-tasks-button-text">Мои задачи</span> <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </a>
                    <div class="dialog my-tasks-dialog" id="my-tasks-dialog">
                        <ul>
                            <li>
                                <a href="{{route('list', [
                                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                                    'user_id' => $current_user->id,
                                    'project_id' => 0,
                                    'filter_type' => 0
                                    ])}}">
                                    Мои задачи
                                </a>
                            </li>
                            <li>
                                <a href="{{route('conversations', ['
                                    workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id])}}"
                                >
                                    Обсуждения
                                </a>
                            </li>
                            <li>
                                <a href="{{route('projects', [
                                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                                    'filter_type' => 0
                                    ])}}"
                                >
                                    Проекты
                                </a>
                            </li>
                            <li>
                                <a href="{{route('favorites')}}">
                                    Избранное
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{route('inbox')}}">
                        Входящие
                        <span class="notification-bell" style="display: {{$current_user->unread_messages > 0 ? 'inline' : 'none'}}"><i class="fa fa-bell" aria-hidden="true"></i></span>
                        <div class="notification-bell-tooltip"></div>
                    </a>
                </div>
                <div class="col-md-4 text-center">
                    <form action="{{route('search')}}" method="get">
                        <input type="text" placeholder="Найти" class="form-control" name="q" style="width: 200px; display: inline-block;" value="{{$search_query}}">
                        <input type="submit" value="Искать" class="btn btn-primary" style="margin-top: -4px; display: inline-block;">
                    </form>
                </div>
                <div class="col-md-2">
                    <div class="dialog workspace-dialog" id="workspace-dialog">
                        <ul>
                            <li>
                                <a href="#" class="workspace-link" data-workspace_id="{{$current_user->workspace_id}}">
                                    {{$current_user->workspace->title}}
                                </a>
                            </li>
                            @forelse($current_user->additional_workspaces as $additional_workspace)
                                @if($additional_workspace->is_visible == 1)
                                    <li>
                                        <a href="#" class="workspace-link" data-workspace_id="{{$additional_workspace->id}}">
                                            {{$additional_workspace->title}}
                                        </a>
                                    </li>
                                @endif
                            @empty
                            @endforelse
                        </ul>
                    </div>
                    <a href="#" id="workspace-dialog-button">
                        {{is_object($current_workspace) ? $current_workspace->title : $current_user->workspace->title}}
                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </a>
                    <input type="hidden"
                           id="workspace-id"
                           value="{{is_object($current_workspace) ? $current_workspace->id : $current_user->workspace_id}}"
                    >
                </div>
                <div class="col-md-2">
                    <a href="#" id="fast-create-dialog-button" class="text-center">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </a>
                    <div class="dialog fast-create-dialog">
                        <ul>
                            <li class="new-task-button"><i class="fa fa-check-circle-o" aria-hidden="true"></i> Задача</li>
                            <li class="new-project-button"><i class="fa fa-list-alt" aria-hidden="true"></i> Проект</li>
                            <li class="new-conversation-button"><i class="fa fa-comment-o" aria-hidden="true"></i> Обсуждение</li>
                        </ul>
                    </div>
                    <div class="dialog user-dialog">
                        <ul>
                            <li><a href="{{route('personal')}}" style="white-space: nowrap;">Личный кабинет</a></li>
                            <li><a href="{{route('logout')}}">Выйти</a></li>
                        </ul>
                    </div>
                    <a href="#"
                       class="users-item"
                       id="user-dialog-button"
                       style="background-color: {{$current_user->color}}; background-image: {{$current_user->background_image}};"
                       data-user_fullname="{{$current_user->fullname}}"
                       data-user_department="{{$current_user->department}}"
                       data-user_office="{{$current_user->office}}"
                    >
                        {{$current_user->first_letters}}
                    </a>
                </div>
            </div>
            @yield('content')
            <div class="container-fluid" id="new-task-full-width" style="display: none">
                <h1 class="text-center" style="margin-top: 20px;">Новая задача</h1>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            @include('tasks.partials.create_task_form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="menu-button">
        <i class="fa fa-bars" aria-hidden="true"></i>
    </div>
    <div class="dialog new-project-dialog popup-small" id="new-project-dialog">
        <h3>Новый проект</h3>
        <input type="hidden" id="owner_id" value="{{Auth::user()->id}}">
        <input type="hidden"
               id="new-project-workspace_id"
               value="{{is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id}}"
        >
        <h3>Название*</h3>
        <input type="text" class="form-control" id="new-project-title" required>
        <h3>Краткое название</h3>
        <input type="text" class="form-control" id="new-project-alias">
        <h3>Описание</h3>
        <div class="for-textarea" id="new-project-text-block">
            <div id="new-project-text-quill-editor"></div>
            <div id="new-project-text-quill-toolbar">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
                <button class="ql-strike"></button>
                <button class="ql-emoji"></button>
            </div>
        </div>
        <div class="divider2"></div>
        <div class="btn btn-primary" id="new-project-create">
            Создать
        </div>
        <div class="new-project-close" id="new-project-close"><i class="fa fa-times" aria-hidden="true"></i></div>
    </div>
    <div class="dialog new-conversation-dialog popup-small" id="new-conversation-dialog">
        <h3>Новое обсуждение</h3>
        @include('tasks.partials.create_conversation')
        <div class="new-conversation-close" id="new-conversation-close"><i class="fa fa-times" aria-hidden="true"></i></div>
    </div>
    <div class="user-popup" id="user-popup">
        <div class="user-popup-info">
            <div class="user-popup-fullname" id="user-popup-fullname"></div>
            <div class="user-popup-department" id="user-popup-department"></div>
            <div class="user-popup-office" id="user-popup-office"></div>
        </div>
    </div>
</div>
@include('tasks.partials.image_modal')
<div class="scroll-button scroll-up-button" id="up"></div>
<div class="scroll-button scroll-down-button" id="down"></div>
<img src="{{asset('img/loading.gif')}}" class="loading">
<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
