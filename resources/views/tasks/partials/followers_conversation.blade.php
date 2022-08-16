<div class="clearfix"></div>
<div class="row followers-block">
    <div class="col-md-12 text-right">
        <span class="followers-list conversation-follower-list">
            @forelse($conversation->followers as $follower)
                <a href="/list/{{$conversation->workspace_id}}/{{$follower->id}}/0/0"
                   class="users-item users-item-small conversation-followers-item-{{$follower->id}}"
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
            @empty
            @endforelse
        </span>
        <span class="dialog-hover-block">
            <span class="add-follower-button">
                <i class="fa fa-user-plus" aria-hidden="true" title="Добавить подписчика"></i>
            </span>
            <ul class="dialog followers-dialog">
                @foreach($conversation->users as $user)
                    @if($user->id != Auth::user()->id)
                        <li data-user_id="{{$user->id}}"
                            data-post_id="{{$conversation->id}}"
                            data-type="conversation"
                            class="conversation-follower-list-item-{{$user->id}}"
                        >{{$user->name}} {!! \App\Models\Follower::isFollower($user->id, 'conversation', $conversation->id) ? '<i class="fa fa-minus-square-o toggle-follower" aria-hidden="true"></i>' : '<i class="fa fa-plus-square-o toggle-follower" aria-hidden="true"></i>' !!}
                        </li>
                    @endif
                @endforeach
            </ul>
        </span>
        <span class="follow-button-block conversation-follow-button-block float-right {{$conversation->is_follower ? 'follow-button-block-active' : ''}}"
              data-post_id="{{$conversation->id}}"
              data-type="conversation"
              style="margin-left: 20px;"
              title="{{$conversation->is_follower ? 'Отписаться' : 'Подписаться'}}"
        >
            <i class="fa fa-bell-o" aria-hidden="true"></i>
        </span>
    </div>
</div>