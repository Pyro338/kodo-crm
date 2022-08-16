<div class="hidden-comments-block"></div>
<div class="hidden-comments-button btn btn-primary">Показать все комментарии</div>
<div class="edit-tasks-comments">
    @if(is_object($current_task) && is_object($current_task->comments))
        @foreach($current_task->comments as $comment)
            @if($comment->recipient_id == 0 && $comment->type != 'task_mention' && $comment->type != 'comment_mention')
                <div class="comment-item {{$comment->class}}"
                     id="comment-item-{{$comment->id}}"
                >
                    <div class="comment-header">
                        {{$comment->date}}
                        <b class="author-string">
                            @if($comment->author_id)
                                <a href="/list/{{$comment->task->workspace_id}}/{{$comment->author_id}}/0/0">{{$comment->author_name}}:</a>
                            @else
                                {{$comment->author_name}}:
                            @endif
                        </b>
                        <div class="comment-delete-button" data-id="{{$comment->id}}">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </div>
                        @if($comment->author_id == Auth::user()->id)
                            <div class="comment-edit-button" data-id="{{$comment->id}}">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </div>
                        @endif
                        <div class="comment-like-button float-right crm-button like-button"
                             id="comment-like-button-{{$comment->id}}"
                             data-id="{{$comment->id}}"
                             data-type="comment"
                             title="{{$comment->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                        >
                            <input type="hidden" id="comment-likes-count-input-{{$comment->id}}" value="{{$comment->likes_count}}">
                            <i class="fa {{$comment->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="comment-likes-count-{{$comment->id}}">{{$comment->likes_count}}</span>
                        </div>
                    </div>
                    <div class="comment-body">
                              <span class="comment-text"
                                    id="comment-text-{{$comment->id}}"
                                    data-content="{{$comment->text}}"
                              >{!! $comment->text !!}</span>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>