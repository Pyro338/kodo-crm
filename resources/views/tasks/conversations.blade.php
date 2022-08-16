@extends('layouts.crm')

@section('content')
    {{csrf_field()}}
    @can('editTasks')
        <input type="hidden" name="current_user" value="{{Auth::user()->id}}" id="current-user">
        <div class="container-fluid text-center page-header">
            <h1>{{$page_title}} <i class="fa fa-plus-circle new-conversation-button" aria-hidden="true"></i></h1>
        </div>
        <div class="container-fluid page-body">
            <div class="row">
                <div class="{{is_object($current_task) ? 'col-md-6 left-panel-conversations' : 'col-md-12 panel-conversations'}}">
                    @if($conversation != null)
                        @include('tasks.partials.conversation_block')
                    @else
                        <div class="conversations-block">
                            <ul id="dialogs">
                                @foreach($conversations as $conversation)
                                    <li id="dialog-item-{{$conversation->id}}"
                                        class="dialog-item"
                                        data-id="{{$conversation->id}}"
                                        data-workspace_id="{{$conversation->workspace_id}}"
                                    >
                                        <div class="dialog-photo">
                                            <a href="#"
                                               class="users-item users-item-big"
                                               style="background-color: {{$conversation->user->color}};
                                                       background-image: {{$conversation->user->background_image}};"
                                               data-user_fullname="{{$conversation->user->fullname}}"
                                               data-user_department="{{$conversation->user->department}}"
                                               data-user_office="{{$conversation->user->office}}"
                                            >
                                                {{$conversation->user->first_letters}}
                                            </a>
                                        </div>
                                        <div class="dialog-content">
                                            <div class="dialog-date">{{$conversation->date}}</div>
                                            <div class="dialog-delete crm-button conversation-delete-button"
                                                 data-id="{{$conversation->id}}"
                                            >
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </div>
                                            <div class="dialog-name">
                                                <a href="{{route('conversations', [
                                                    'workspace_id' => $current_workspace->id,
                                                    'conversation_id' => $conversation->id
                                                    ])}}"
                                                >
                                                    {{$conversation->title}}
                                                </a>
                                            </div>
                                            <div class="dialog-text-preview"
                                                 data-id="{{$conversation->id}}"
                                                 data-workspace_id="{{$conversation->workspace_id}}"
                                            >
                                                {!! $conversation->text_preview !!}
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                @if(is_object($current_task))
                    <div class="col-md-6 right-panel-edit"
                         style="{{$conversation ? 'display: block' : ''}}"
                    >
                        @include('tasks.partials.task_block')
                    </div>
                    @include('tasks.partials.upload_form')
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            У вас нет прав для просмотра данной страницы
        </div>
    @endcan
    <script src="{{ asset('js/drag_and_drop.js') }}"></script>
    <script src="{{asset('js/pages_scripts/conversations.js')}}"></script>
@endsection