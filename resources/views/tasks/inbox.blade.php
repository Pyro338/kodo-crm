@extends('layouts.crm')

@section('content')
    @can('editTasks')
        <div class="container-fluid text-center page-header">
            <h1>Входящие</h1>
            <span class="page-header-submenu page-header-submenu-active">Активные</span><span class="page-header-submenu">Архив</span>
        </div>
        <div class="container-fluid page-body">
            <div class="row">
                <div class="col-md-12 left-panel" id="inbox-active">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a href="#" id="all-to-arhive" class="inbox-heading">Отправить все в архив</a>
                            <label class="float-right inbox-heading text-right">
                                <input type="checkbox" name="subscribe" id="subscribe-radio" @if(Auth::user()->subscribe == 1)checked="checked"@endif>
                                Email оповещения
                            </label>
                        </div>
                        <div class="panel-body comments-active-body">
                            @foreach($active_comments as $comment)
                                <div class="inbox-item row" data-id="{{$comment->id}}" id="inbox-item-{{$comment->id}}">
                                    <div class="col-md-11">
                                        @if($comment->task_id)
                                            <div>
                                                <span class="complete-button complete-status-{{$comment->task->status}}">
                                                    <i class="fa fa-check-circle-o" aria-hidden="true"></i>
                                                </span>
                                                <span id="comment-task-item-{{$comment->task_id}}">
                                                        Задача
                                                    @if(is_object($comment->task) && $comment->task->project_id)в
                                                    <span class="project-title project-title-{{$comment->task_id}}">
                                                            <a href="/list/{{$comment->task->workspace_id}}/0/{{$comment->task->project_id}}/1"
                                                               style="background-color: {{$comment->task->project->color}}"
                                                            >
                                                                {{$comment->task->project->title}}
                                                            </a>
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                            <h3>
                                                <a href="/list/0/{{Auth::user()->id}}/0/0{{$comment->task_id}}" class="task-link"
                                                   data-id="{{$comment->task_id}}" id="task-link-{{$comment->task_id}}">
                                                    @if($comment->alias)
                                                        <b>{{$comment->alias}} </b>
                                                    @endif
                                                    {{$comment->task->title}}
                                                </a>
                                            </h3>
                                        @elseif($comment->conversation_id)
                                            <div>
                                                    <span id="comment-conversation-item-{{$comment->conversation->id}}">
                                                        Обсуждение
                                                    </span>
                                            </div>
                                            <h3>
                                                <a href="/conversations/{{$comment->conversation->workspace_id}}/{{$comment->conversation->id}}">
                                                    {{$comment->conversation->title}}
                                                </a>
                                            </h3>
                                        @endif
                                        <p>
                                            <b>
                                                @if($comment->type == 'comment')
                                                    <a href="/list/0/{{$comment->author_id}}/0/0">
                                                        {{$comment->author_name}}
                                                    </a>
                                                @else{{$comment->author_name}}
                                                @endif:
                                            </b>
                                            {!!$comment->text !!}
                                            <small>{{$comment->created_at}}</small>
                                        </p>
                                        <hr>
                                    </div>
                                    <div class="col-md-1 move-to-archive" data-id="{{$comment->id}}">
                                        <i class="fa fa-times" id="move-to-archive-{{$comment->id}}" aria-hidden="true" title="Отправить в архив"></i>
                                        <i class="fa fa-undo" id="move-to-active-{{$comment->id}}" aria-hidden="true" title="Вернуть"
                                           style="display: none"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div id="id-token-active" data-token="{{csrf_token()}}" data-last-page="{{$archive_comments->lastPage()}}" style="display: none">
                    </div>
                </div>
                <div class="col-md-12 left-panel" id="inbox-arhive" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <a href="#" id="clear-archive">Отчистить архив</a>
                        </div>
                        <div class="panel-body comments-arhive-body">
                            @foreach($archive_comments as $comment)
                                <div class="inbox-item row" data-id="{{$comment->id}}" id="inbox-item-{{$comment->id}}">
                                    <div class="col-md-11">
                                        @if($comment->task_id)
                                            <div>
                                                    <span class="complete-button complete-status-{{$comment->task->status}}">
                                                        <i class="fa fa-check-circle-o" aria-hidden="true"></i>
                                                    </span>
                                                <span>Задача
                                                    @if(is_object($comment->task) && $comment->task->project_id)в
                                                    <span class="project-title project-title-{{$comment->task_id}}">
                                                            <a href="/list/0/0/{{$comment->task->project_id}}/1"
                                                               style="background-color: {{$comment->task->project->color}}"
                                                            >
                                                                {{$comment->task->project->title}}
                                                            </a>
                                                        </span>
                                                    @endif
                                                    </span>
                                            </div>
                                            <h3>
                                                <a href="/list/0/{{Auth::user()->id}}/0/0{{$comment->task_id}}" class="task-link"
                                                   data-id="{{$comment->task_id}}" id="task-link-{{$comment->task_id}}">
                                                    {{$comment->task->title}}
                                                </a>
                                            </h3>
                                        @elseif($comment->conversation_id)
                                            <div>
                                                    <span id="comment-conversation-item-{{$comment->conversation->id}}">
                                                        Обсуждение
                                                    </span>
                                            </div>
                                            <h3>
                                                <a href="/conversations/{{$comment->conversation->id}}">
                                                    {{$comment->conversation->title}}
                                                </a>
                                            </h3>
                                        @endif
                                        <p>
                                            <b>
                                                @if($comment->type == 'comment')
                                                    <a href="/list/0/{{$comment->author_id}}/0/0">
                                                        {{$comment->author_name}}
                                                    </a>
                                                @else{{$comment->author_name}}
                                                @endif:
                                            </b>
                                            {!!$comment->text !!}
                                            <small>{{$comment->created_at}}</small>
                                        </p>
                                        <hr>
                                    </div>
                                    <div class="col-md-1 move-to-archive" data-id="{{$comment->id}}">
                                        <i class="fa fa-times" id="move-to-archive-{{$comment->id}}" aria-hidden="true" title="Отправить в архив"
                                           style="display: none"></i>
                                        <i class="fa fa-undo" id="move-to-active-{{$comment->id}}" aria-hidden="true" title="Вернуть"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="id-token-archive" data-token="{{csrf_token()}}" data-last-page="{{$archive_comments->lastPage()}}" style="display: none">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 right-panel-edit">
                    @include('tasks.partials.task_block')
                </div>
                @include('tasks.partials.upload_form')
            </div>
        </div>
        <input type="hidden" id="notification-check" value="0">
        @include('tasks.partials.image_modal')
        <script src="{{ asset('js/drag_and_drop.js') }}"></script>
    @else
        <div class="alert alert-danger">
            У вас нет прав для просмотра данной страницы
        </div>
    @endcan
    <script src="{{asset('js/pages_scripts/inbox.js')}}"></script>
@endsection