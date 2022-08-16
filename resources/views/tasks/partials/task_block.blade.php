<div class="panel panel-default edit-task">
    @include('tasks.partials.edit_form')
    <div class="panel-body">
        <div class="edit-tasks-files">
            @if(is_object($current_task) && is_object($current_task->files))
                @foreach($current_task->files as $file)
                    <div class="file-like-button float-right crm-button like-button"
                         id="file-like-button-{{$file->id}}"
                         data-id="{{$file->id}}"
                         data-type="file"
                         title="{{$file->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                    >
                        <input type="hidden" id="file-likes-count-input-{{$file->id}}" value="{{$file->likes_count}}">
                        <i class="fa {{$file->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="file-likes-count-{{$file->id}}">{{$file->likes_count}}</span>
                    </div>
                    <div class="file-link" id="small-file-item-{{$file->id}}">
                        <a href="{{route('download', $file->id)}}">
                            <img src="/img/fileicons/{{$file->type}}.png" alt="{{$file->type}}" class="fileicon">
                            {{$file->original_filename}}
                        </a>
                        <span class="file-delete" data-id="{{$file->id}}">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </span>
                        @if($file->type == 'jpg' || $file->type == 'jpeg' || $file->type == 'png'
                        || $file->type == 'gif')
                            <br/>
                            <div class="text-center">
                                <img src="/download/{{$file->id}}"
                                     class="{{$file->class}}"
                                     data-url="/download/{{$file->id}}"
                                     alt="{{$file->alt}}"
                                     title="{{$file->alt}}"
                                >
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
        @include('tasks.partials.comments_block')
        @include('tasks.partials.comment_form')
        @include('tasks.partials.followers')
    </div>
</div>