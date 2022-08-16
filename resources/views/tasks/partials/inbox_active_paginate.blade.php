@foreach($active_comments as $comment)
    <div class="comment-item row" data-id="{{$comment->id}}" id="comment-item-{{$comment->id}}">
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
            <i class="fa fa-times" id="move-to-archive-{{$comment->id}}" aria-hidden="true" title="Отправить в архив"></i>
            <i class="fa fa-undo" id="move-to-active-{{$comment->id}}" aria-hidden="true" title="Вернуть"
               style="display: none"></i>
        </div>
    </div>
@endforeach