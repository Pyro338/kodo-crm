@extends('layouts.crm')

@section('content')
    @can('editTasks')
        <div class="container-fluid text-center page-header">
            <h1>
                {{$page_title}}
                @if($filter_type == 1)
                    <i class="fa fa-pencil project-edit-button" aria-hidden="true" id="project-edit-button-{{$project->id}}"
                       data-id="{{$project->id}}"></i>
                @endif
            </h1>
            <span class="page-header-submenu page-header-submenu-active" id="page-header-list">Список</span>
            <span class="page-header-submenu" id="page-header-calendar">Календарь</span>
            <span class="page-header-submenu" id="page-header-files">Файлы</span>
        </div>
        <div class="container-fluid page-body">
            <div class="row" id="my_tasks-list">
                <div class="@if($task_id == 0)col-md-12 @else col-md-6 @endif left-panel">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            @if($filter_type == 0 || $filter_type == 1)
                                <div class="btn btn-primary btn-without-margin create-task-button">Добавить задачу</div>
                                <div class="btn btn-primary btn-without-margin create-section-button">Добавить секцию</div>
                            @endif
                            @if($filter_type != 5)
                                <span data-toggle="dropdown" class="float-right filter">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                            </span>
                                <div class="dropdown-menu">
                                    <h4>Вид</h4>
                                    <div class="filter-dialog-radio">
                                        <label>
                                            <input type="radio" name="survey" id="incompletable-radio" value="incompletable" checked>
                                            Незавершенные задачи
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="survey" id="completable-radio" value="completable">
                                            Завершенные задачи
                                        </label>
                                    </div>
                                    <div class="radio disabled">
                                        <label>
                                            <input type="radio" name="survey" id="all-radio" value="all">
                                            Все задачи
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="panel-body">
                            @if($filter_type == 0)
                                <h3 class="new-tasks-header tasks-header">
                                <span class="new-tasks-opened tasks-opened">
                                   <i class="fa fa-caret-right" aria-hidden="true"></i>
                                </span>
                                    <span class="new-tasks-closed tasks-closed">
                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </span>
                                    <span>Новые задачи</span>
                                </h3>
                                <div class="new-tasks-body tasks-body"></div>
                                <h3 class="today-tasks-header tasks-header">
                                <span class="today-tasks-opened tasks-opened">
                                   <i class="fa fa-caret-right" aria-hidden="true"></i>
                                </span>
                                    <span class="today-tasks-closed tasks-closed">
                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </span>
                                    <span>Сегодня</span>
                                </h3>
                                <div class="today-tasks-body tasks-body"></div>
                                <h3 class="upcoming-tasks-header tasks-header">
                                <span class="upcoming-tasks-opened tasks-opened">
                                    <i class="fa fa-caret-right" aria-hidden="true"></i>
                                </span>
                                    <span class="upcoming-tasks-closed">
                                    <i class="fa fa-caret-down tasks-closed" aria-hidden="true"></i>
                                </span>
                                    <span>Предстоит</span>
                                </h3>
                                <div class="upcoming-tasks-body tasks-body"></div>
                            @endif
                            <div class="tasks-body {{$tasks_body_class}}">
                                @foreach($tasks as $task)
                                    @if(($filter_type !=4 && $task->is_visible == 1) || ($filter_type ==4 && $task->is_visible == 0))
                                        @if((is_object($current_workspace) && $task->workspace_id == $current_workspace->id) || !is_object($current_workspace))
                                            @if($task->is_private != 1 || $task->is_private && $current_user->id == $task->owner_id || \App\Models\Follower::isFollower($current_user->id, 'task', $task->id))
                                                @if((is_object($task->project) && in_array($task->project->workspace_id, $current_user->workspaces_list))
                                                || (is_object($current_workspace)
                                                    && $task->workspace_id == $current_workspace->id)
                                                )
                                                    <div class="task-item task-visibly-status-{{$task->status}} {{$task->mark_class}} @if($task->id == $task_id || ($current_task != null && $current_task->parent_task != null && $current_task->parent_task->id == $task->id))task-selected btn-primary @endif"
                                                         id="task-item-{{$task->id}}"
                                                    >
                                                        <input type="hidden" name="sort[]" value="{{$task->id}}">
                                                        <span class="drag-button">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </span>
                                                        <span class="complete-button complete-status-{{$task->status}}"
                                                              data-id="{{$task->id}}"
                                                              id="complete-button-{{$task->id}}"
                                                        >
                                                        <i class="fa fa-check-circle-o" aria-hidden="true"></i>
                                                    </span>
                                                        <span class="task-title">
                                                        <a href="{{route('list', [
                                                                'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                                                                'user_id' => $current_user->id,
                                                                'project_id' => 0,
                                                                'filter_type' => 0,
                                                                'task_id' => $task->id
                                                                ])}}"
                                                           class="task-link"
                                                           data-id="{{$task->id}}"
                                                           id="task-link-{{$task->id}}"
                                                        >
                                                            @if($task->project)
                                                                <b>{{$task->project->alias}} </b>
                                                            @endif
                                                            {{$task->title}}
                                                        </a>
                                                    </span>
                                                        @if($filter_type == 4)
                                                            <div class="float-right restore-task-button"
                                                                 id="restore-task-button-{{$task->id}}"
                                                                 data-id="{{$task->id}}"
                                                            >
                                                                <i class="fa fa-undo" aria-hidden="true" title="Восстановить"></i>
                                                            </div>
                                                        @endif
                                                        @if($filter_type == 0)
                                                            <div class="float-right time-mark-button" data-id="{{$task->id}}"></div>
                                                            <div class="dialog mark-dialog" id="mark-dialog-{{$task->id}}">
                                                                <h3>Пометить задачу</h3>
                                                                <ul>
                                                                    <li class="mark-new time-mark"
                                                                        data-id="{{$task->id}}"
                                                                        data-mark="new"
                                                                    >
                                                                        Новая
                                                                    </li>
                                                                    <li class="mark-today time-mark"
                                                                        data-id="{{$task->id}}"
                                                                        data-mark="today"
                                                                    >
                                                                        Сегодня
                                                                    </li>
                                                                    <li class="mark-upcoming time-mark"
                                                                        data-id="{{$task->id}}"
                                                                        data-mark="upcoming"
                                                                    >
                                                                        Предстоящая
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        @if($filter_type != 0)
                                                            <div class="float-right asignee-button users-item users-item-small"
                                                                 data-id="{{$task->id}}"
                                                                 data-user_id="{{$task->implementer_id}}"
                                                                 data-user_fullname="{{$task->user->fullname}}"
                                                                 data-user_department="{{$task->user->department}}"
                                                                 data-user_office="{{$task->user->office}}"
                                                                 style="background-color: {{$task->user->color}}; background-image: {{$task->user->background_image}};"
                                                                 id="asignee-button-{{$task->id}}"
                                                            >
                                                                {{$task->user->first_letters}}
                                                            </div>
                                                            <div class="dialog asignee-dialog"
                                                                 data-id="{{$task->id}}"
                                                                 id="asignee-dialog-{{$task->id}}"
                                                            >
                                                                <h3>Назначить задачу</h3>
                                                                <select id="asignee-select-{{$task->id}}"
                                                                        class="asignee-select"
                                                                        data-id="{{$task->id}}"
                                                                >
                                                                    <option value="{{$task->implementer_id}}" selected class="form-control">
                                                                        {{$task->user->name}}
                                                                    </option>
                                                                    @foreach($users as $user)
                                                                        @if($user->id != $task->implementer_id)
                                                                            <option value="{{$user->id}}" class="asignee-option">
                                                                                {{$user->name}}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                <p id="asignee-user-link-{{$task->id}}">
                                                                    <a href="/list/{{$task->workspace_id}}/{{$task->implementer_id}}/0/0">
                                                                        Посмотреть задачи пользователя {{$task->user->name}}
                                                                    </a>
                                                                </p>
                                                            </div>
                                                        @endif
                                                        @if($filter_type != 1 && $task->project)
                                                            <div class="float-right project-title">
                                                                <a href="{{route('list', [
                                                                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                                                                    'user_id' => $current_user->id,
                                                                    'project_id' => $task->project_id,
                                                                    'filter_type' => 1
                                                                    ])}}"
                                                                   style="background-color: {{$task->project->color}}">
                                                                    {{$task->project->title}}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            <!--if public/private-->
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 right-panel-edit" style="display: @if($task_id == 0)none @else block @endif ;">
                    @include('tasks.partials.task_block')
                </div>
                @include('tasks.partials.upload_form')
            </div>
            <div class="row" id="my_tasks-calendar">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body calendar-list">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="my_tasks-files">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body files-list">
                            @forelse($files as $file)
                                @forelse($tasks as $task)
                                    @if(($file->task_id == $task->id
                                        && $task->is_visible == 1
                                        && !is_object($current_workspace)
                                        && $file->is_visible == 1)
                                        || (is_object($current_workspace)
                                        && $file->workspace_id == $current_workspace->id
                                        && $file->task_id == $task->id
                                        && $file->is_visible == 1))
                                        <div class="file-item" id="file-item-{{$file->id}}">
                                            <div class="file-list-author">
                                                <a href="/list/{{$task->workspace_id}}/{{$file->author}}/0/0"
                                                   class="users-item"
                                                   data-user_fullname="{{--$file->user->fullname--}}"
                                                   data-user_department="{{--$file->user->department--}}"
                                                   data-user_office="{{--$file->user->office--}}"
                                                   style="background-color: {{--$file->user->color--}};
                                                           background-image: {{--$file->user->background_image--}};
                                                           "
                                                >
                                                    {{--$file->user->first_letters--}}
                                                </a>
                                            </div>
                                            <div class="file-list-name">
                                                @if($file->type == 'jpg' || $file->type == 'jpeg' || $file->type == 'png'
                                                || $file->type == 'gif')
                                                    <div class="img-preview" data-url="{{route('download', $file->id)}}"
                                                         style="background-image: url('{{route('download', $file->id)}}')">
                                                    </div>
                                                @endif
                                                <a href="{{route('download', $file->id)}}">
                                                    <img class="fileicon" src="/img/fileicons/{{$file->type}}.png" alt="{{$file->type}}">
                                                    {{$file->original_filename}}
                                                </a>
                                                <span class="file-delete" data-id="{{$file->id}}">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <div class="file-list-task">
                                                Задача:
                                                <a href="/list/{{$task->workspace_id}}/{{$current_user->id}}/0/0/{{$task->id}}" class="task-link" data-id="{{$task->id}}">
                                                    {{$file->task->title}}
                                                </a>
                                            </div>
                                            <hr>
                                        </div>
                                    @endif
                                @empty
                                    <h3 class="no-files-label">Пока не загружено никаких файлов</h3>
                                @endforelse
                            @empty
                                <h3 class="no-files-label">Пока не загружено никаких файлов</h3>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($filter_type == 1)
            @include('tasks.partials.edit_project')
        @endif
        <script src="{{ asset('js/drag_and_drop.js') }}"></script>
    @else
        <div class="alert alert-danger">
            У вас нет прав для просмотра данной страницы
        </div>
    @endcan
    <script src="{{asset('js/pages_scripts/list.js')}}"></script>
@endsection