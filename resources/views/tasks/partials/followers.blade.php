<hr/>
<div class="row">
    <div class="col-md-8">
        Подписчики:
        <span class="followers-list task-followers-list">
            @if(isset($current_task->followers))
                @foreach($current_task->followers as $follower)
                    <a href="/list/{{$follower->id}}/0/0"
                       class="users-item task-followers-item-{{$follower->id}}"
                       data-user_fullname="{{$follower->fullname}}"
                       data-user_department="{{$follower->department}}"
                       data-user_office="{{$follower->office}}"
                       style="background-color: {{$follower->color}};
                               margin-left: 0;
                               margin-right: 0;
                               background-image: {{$follower->background_image}};"
                    >
                    {{$follower->first_letters}}
                </a>
                @endforeach
            @endif
        </span>
        <span class="dialog-hover-block">
            <span class="add-follower-button">
                <i class="fa fa-user-plus" aria-hidden="true" title="Добавить подписчика"></i>
            </span>
            <ul class="dialog followers-dialog">
                @foreach($users as $user)
                    @if($user->id != $current_user->id)
                        <li data-user_id="{{$user->id}}"
                            data-post_id="{{$task_id}}"
                            data-type="task"
                            id="follower-list-item-{{$user->id}}"
                            class="task-follower-list-item-{{$user->id}}"
                        >
                                {{$user->name}}
                            @if(\App\Models\Follower::isFollower($user->id, 'task', $task_id))
                                <i class="fa fa-minus-square-o toggle-follower" aria-hidden="true"></i>
                            @else
                                <i class="fa fa-plus-square-o toggle-follower" aria-hidden="true"></i>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        </span>
    </div>
    <div class="col-md-4">
        <span class="follow-button-block task-follow-button-block float-right {{\App\Models\Follower::isFollower($current_user->id, 'task', $task_id) ? 'follow-button-block-active' : ''}}"
              data-post_id="{{$task_id}}"
              data-type="task">
            <i class="fa fa-bell-o" aria-hidden="true"></i>
            <span class="follow-button-text">
                {{\App\Models\Follower::isFollower($current_user->id, 'task', $task_id) ? 'Отписаться' : 'Подписаться'}}
            </span>
        </span>
    </div>
</div>