<div class="panel panel-default conversations-item {{is_object($current_task) ? 'panel-full-height-small' : 'panel-full-height'}}"
     id="conversations-item-{{$conversation->id}}"
>
    <div class="panel-heading">
        @if(is_object($conversation->task))
            <div class="float-right crm-button conversation-close-button"
                 style="{{!is_object($current_task) ? 'display: none' : ''}}"
                 title="Закрыть панель"
            >
                <i class="fa fa-times" aria-hidden="true"></i>
            </div>
        @endif
        <div class="float-right conversation-header-button conversation-delete-button"
             data-id="{{$conversation->id}}"
             data-single="yes"
             title="Удалить обсуждение"
        >
            <i class="fa fa-trash-o" aria-hidden="true"></i>
        </div>
        @if(is_object($conversation->task))
            <div class="float-right crm-button show-task-button"
                 title="Перейти к задаче"
                 style="{{is_object($current_task) ? 'display: none' : ''}}"
            >
                <a href="{{route('conversations', [
                    'workspace_id' => $current_workspace->id,
                    'conversation_id' => $conversation->id
                    ])}}"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
            </div>
        @endif
        <div class="crm-button float-left" style="margin-left: 0; margin-right: 20px">
            <a href="{{route('conversations', ['workspace_id' => $current_workspace->id])}}"
               title="Вернуться к списку обсуждений"
            >
                <i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>
            </a>
        </div>
        <div class="conversation-like-button float-right crm-button like-button"
             id="conversation-like-button-{{$conversation->id}}"
             data-id="{{$conversation->id}}"
             data-type="conversation"
             title="{{$conversation->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
        >
            <input type="hidden" id="conversation-likes-count-input-{{$conversation->id}}" value="{{$conversation->likes_count}}">
            <i class="fa {{$conversation->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="conversation-likes-count-{{$conversation->id}}">{{$conversation->likes_count}}</span>
        </div>
        @include('tasks.partials.followers_conversation')
    </div>
    <div class="panel-body">
        <div class="message-block" id="message-block-{{$conversation->id}}">
            @foreach($conversation->messages as $message)
                <div class="message-item {{$message->class}}" id="message-item-{{$message->id}}">
                    <div class="message-photo">
                        <a href="#"
                           class="users-item users-item-big"
                           style="background-color: {{$message->user->color}};
                                   background-image: {{$message->user->background_image}};"
                           data-user_fullname="{{$message->user->fullname}}"
                           data-user_department="{{$message->user->department}}"
                           data-user_office="{{$message->user->office}}"
                        >
                            {{$message->user->first_letters}}
                        </a>
                    </div>
                    <div class="message-heading">
                        <b class="message-author">{{$message->author}}</b>
                        @if($message->author_id == $current_user->id)
                            <small class="float-right crm-button delete-message-button"
                                   id="delete-message-button-{{$message->id}}"
                                   data-id="{{$message->id}}"
                            >
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </small>
                        @endif
                        <small class="float-right crm-button reply-button reply-button-{{$message->id}}"
                               data-id="{{$message->id}}"
                        >
                            <i class="fa fa-reply" aria-hidden="true"></i>
                        </small>
                        <div class="message-like-button float-right crm-button like-button"
                             id="message-like-button-{{$message->id}}"
                             data-id="{{$message->id}}"
                             data-type="message"
                             title="{{$message->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                        >
                            <input type="hidden" id="message-likes-count-input-{{$message->id}}" value="{{$message->likes_count}}">
                            <i class="fa {{$message->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="message-likes-count-{{$message->id}}">{{$message->likes_count}}</span>
                        </div>
                        <small class="float-right">{{$message->time}}</small>
                    </div>
                    <div class="message-body">
                        {!! $message->text !!}
                    </div>
                    @if($message->attachment)
                        <div class="message-attachment">
                            <img class="img-small-preview" src="{{route('download', $message->attachment_id)}}"
                                 style="max-width: 50%; margin-top: 10px; margin-bottom: 10px;"
                                 data-url="{{route('download', $message->attachment_id)}}">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="panel-footer">
    @include('tasks.partials.create_message')
</div>