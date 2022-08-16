@foreach($projects_to_main as $project)
    @if(is_object($current_workspace) && $project->workspace_id == $current_workspace->id)
        <div class="panel panel-default" id="project-item-{{$project->id}}">
            <div class="panel-heading">
                <h3>
                    <a href="{{route('list', [
                                        'workspace_id' => $project->workspace_id,
                                        'user_id' => 0,
                                        'project_id' => $project->id,
                                        'filter_type' => 1
                                        ])}}" id="project-title-{{$project->id}}">
                    {{$project->title}} <!--small>Отдел: "{{$project->workspace->title}}"</small-->
                    </a>
                    @if($filter_type == 0)
                        <div class="float-right edit-project crm-button" data-id="{{$project->id}}">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </div>
                    @endif
                    @if($filter_type == 1)
                        <div class="float-right restore-project crm-button" data-id="{{$project->id}}">
                            <i class="fa fa-undo" aria-hidden="true" title="Восстановить"></i>
                        </div>
                    @endif
                    <div class="project-like-button float-right crm-button like-button"
                         id="project-like-button-{{$project->id}}"
                         data-id="{{$project->id}}"
                         data-type="project"
                         title="{{$project->is_liked ? 'Убрать из избранного' : 'Добавить в избранное'}}"
                    >
                        <input type="hidden" id="project-likes-count-input-{{$project->id}}" value="{{$project->likes_count}}">
                        <i class="fa {{$project->is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}}" aria-hidden="true"></i> <span id="project-likes-count-{{$project->id}}">{{$project->likes_count}}</span>
                    </div>
                </h3>
            </div>
            <div class="panel-body">
                <div id="project-text-{{$project->id}}">{!!$project->text !!}</div>
                <h4>Задач в проекте</h4>
                <table class="table text-center">
                    <tr>
                        <th>Всего</th>
                        <th>Выполненно</th>
                        <th>В работе</th>
                    </tr>
                    <tr>
                        <td>{{$project->all_tasks_count}}</td>
                        <td>{{$project->complete_tasks_count}}</td>
                        <td>{{$project->incomplete_tasks_count}}</td>
                    </tr>
                </table>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {{$project->complete_percent}}%"
                         aria-valuenow="{{$project->complete_percent}}" aria-valuemin="0" aria-valuemax="100">
                        {{$project->complete_percent}}%
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach