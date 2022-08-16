<form method="post" class="form-horisontal" id="task-edit-form">
    {{csrf_field()}}
    <input type="hidden" name="user_id" value="{{isset($user_id) ? $user_id : $current_user->id}}" id="user-id">
    <input type="hidden" name="current_user" value="{{$current_user->id}}" id="current-user">
    <input type="hidden" name="project_id" value="@if($filter_type == 1){{$project->id}}@endif" id="create-task-project-input">
    <input type="hidden" name="id" value="@if($current_task != null){{$current_task->id}}@endif" id="edit-task-id-input">
    <input type="hidden" name="status" value="@if($current_task != null){{$current_task->status}}@endif" id="edit-task-status-input">
    <input type="hidden" name="project_id" value="@if($current_task != null){{$current_task->project_id}}@endif" id="edit-task-project-input">
    <input type="hidden"
           name="workspace_id"
           value="@if($current_task != null){{$current_task->workspace_id}}@endif"
           id="edit-task-workspace-input"
    >
    <input type="hidden"
           name="additional_users"
           id="edit-tasks-additional_users-input"
           value="@if($current_task != null){{$current_task->additional_users}}@endif"
    >
    <input type="hidden" name="mentions" id="edit-task-mentions">
    <div style="display: none" id="task-url"></div>
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6">
                <select name="delegated_id"
                        class="form-control"
                        id="edit-task-delegated-input"
                        style="@if($current_task != null && $current_task->parent_task)display: none @endif"
                >
                    <option value="@if($current_task != null){{$current_task->implementer_id}}@endif"
                            selected id="edit-task-implementer">
                        @if($current_task != null){{$current_task->user->name}}@endif
                    </option>
                    @foreach($users as $user)
                        @if($current_task != null && $user->id != $current_task->implementer_id)
                            <option value="{{$user->id}}" class="edit-task-other-users">{{$user->name}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <span class="float-right crm-button" id="close-edit-task-panel" title="Закрыть">
                     <i class="fa fa-times" aria-hidden="true"></i>
                </span>
                <div class="float-right crm-button" id="task-menu-button">
                    <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                </div>
                <span class="float-right crm-button" id="copy-url-button" title="Скопировать ссылку на задачу">
                    <i class="fa fa-clipboard" aria-hidden="true"></i>
                </span>
                <div class="float-right crm-button"
                     id="create-subtask-button"
                     title="Создать подзадачу"
                     style="@if($current_task != null && $current_task->parent_task)display: none @endif"
                >
                    <i class="fa fa-sitemap" aria-hidden="true"></i>
                </div>
                <div class="float-right crm-button" id="edit-task-attach-button" title="Добавить файл">
                     <i class="fa fa-file-o" aria-hidden="true"></i>
                </div>
                <div class="float-right crm-button"
                      id="task-like-button"
                      title="@if($current_task != null && $current_task->is_liked) Убрать из избранного @else Добавить в избранное @endif"
                >
                    <input type="hidden" id="task-likes-count-input" value="{{$current_task ? $current_task->likes_count : 0}}">
                    <i class="fa @if($current_task != null && $current_task->is_liked) fa-thumbs-up @else fa-thumbs-o-up @endif" aria-hidden="true"></i> <span id="task-likes-count">{{$current_task ? $current_task->likes_count : 0}}</span>
                </div>
                <div class="dialog task-menu-dialog">
                    <ul>
                        <li>
                            <span class="crm-button"
                                  id="tags-button"
                                  style="@if($current_task != null && $current_task->parent_task)display: none @endif"
                            >
                                <i class="fa fa-tags" aria-hidden="true"></i> Метки
                            </span>
                        </li>
                        <li>
                            <span class="crm-button" id="edit-task-delete-button">
                                <i class="fa fa-trash-o" aria-hidden="true"></i> Удалить задачу
                            </span>
                        </li>
                        <li>
                            <span class="float-right crm-button"
                                  id="toggle-section-button"
                                  style="@if($current_task != null && $current_task->parent_task)display: none @endif"
                            >
                                <i class="fa fa-tasks" aria-hidden="true"></i>
                                <span class="toggle-section-button-text">
                                @if($current_task != null && $current_task->status == 4)
                                        Преобразовать в задачу
                                    @else
                                        Преобразовать в секцию
                                    @endif
                                </span>
                            </span>
                        </li>
                        <li>
                            <span class="crm-button"
                                  id="private-button"
                                  style="@if($current_task != null && $current_task->parent_task)display: none @endif"
                            >
                                <i class="fa fa-eye" aria-hidden="true"></i>
                                <span class="private-button-text">
                                @if($current_task != null && $current_task->is_private == 1)
                                        Сделать публичной
                                    @else
                                        Сделать приватной
                                    @endif
                                </span>
                            </span>
                        </li>
                        <li>
                            <span class="crm-button"
                                  id="task-conversation-button"
                                  style="{{isset($conversation) ? 'display: none' : ''}}"
                                  @if($current_task != null && is_object($current_task->conversation))data-conversation_id="{{$current_task->conversation->id}}" @else data-conversation_id="" @endif
                            >
                                <i class="fa fa-comment-o" aria-hidden="true"></i> Обсудить задачу
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="dialog tags-dialog">
                    <h3>Метки</h3>
                    <div class="tags-list" id="tags-list">
                        @if($current_task)
                            @foreach($tags as $tag)
                                @if(!in_array($tag->id, $current_task->used_tags_list))
                                    <div class="tags-item tags-item-select"
                                         id="tag-{{$tag->id}}"
                                         data-id="{{$tag->id}}"
                                         style="background-color: {{$tag->color}}"
                                         title="Добавить к задаче"
                                    >
                                        <span class="tags-item-select-title"
                                              data-id="{{$tag->id}}"
                                        ><i class="fa fa-tag" aria-hidden="true"></i> {{$tag->title}}</span>
                                        <span class="delete-tag crm-button"
                                              data-id="{{$tag->id}}"
                                              title="Удалить метку"
                                        >
                                            <i class="fa fa-times" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                    <input type="text"
                           placeholder="Новая метка"
                           style="width: 70%; display: inline-block"
                           id="new-tag-input"
                    >
                    <div class="btn btn-primary"
                         style="display: inline-block; margin-top: -4px"
                         id="new-tag-button"
                    >
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div class="select-project-block"
                     id="select-project-block"
                     style="@if($current_task != null && $current_task->parent_task)display: none @endif"
                >
                        <a href="{{route('list', [
                            'workspace_id' => $current_task ? $current_task->workspace_id : $current_user->workspace_id,
                            'user_id' => $current_task ? $current_task->delegated_id : $current_user->id,
                            'project_id' => 0,
                            'filter_type' => 0
                            ])}}"
                           class="crm-button back-to-user-button"
                           style="margin-left: 0; margin-right: 10px;"
                           title="Перейти к списку задач"
                        >
                            <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
                        </a>
                    <span class="edit-add-task-to-project">
                        <span>
                        @if($current_task != null)
                            @if($current_task->project){{$current_task->project->title}} @elseДобавить к проекту @endif
                        @elseДобавить к проекту @endif
                        </span>
                    </span>
                    <span class="clear-project" style="
                    @if($current_task)
                    {{$current_task->project_id == 0 ? 'display: none' : ''}}
                    @endif
                            ">
                        <i class="fa fa-ban" aria-hidden="true"></i>
                    </span>
                    <div class="dialog edit-task-to-project-dialog">
                        <ul>
                            @foreach($projects as $project)
                                @if((is_object($current_workspace) && $project->workspace_id == $current_workspace->id)
                                || (!is_object($current_workspace) && $project->workspace_id == $current_user->workspace->id))
                                    <li class="edit-add-task-to-project-dialog-project"
                                        data-id="{{$project->id}}"
                                        data-workspace_id="{{$project->workspace_id}}"
                                    >
                                        {{$project->title}}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="parent-tasks-block" id="parent-tasks-block">
                    @if($current_task != null && $current_task->parent_task)
                        <div class="parent-tasks-item">
                            <a class="task-link" data-id="{{$current_task->parent_task->id}}">
                                <i class="fa fa-arrow-circle-o-left" aria-hidden="true"> {{$current_task->parent_task->title}}</i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 text-right">
                <input type="text"
                       id="edit-task-due-date-input"
                       name="due_date"
                       value="{{$current_task ? $current_task->due_date : ''}}"
                       class="datepicker"
                       placeholder="Назначить дату"
                ><span class="clear-due_date"><i class="fa fa-ban" aria-hidden="true"></i></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                 <span class="@if($current_task != null && $current_task->status == 3)edit-project-status-active @else edit-project-status @endif"
                       id="edit-project-status"
                 >
                       <i class="fa fa-check-circle-o" aria-hidden="true"></i>
                 </span>
            </div>
            <div class="col-md-11">
                <input type="text"
                       name="title"
                       class="form-control"
                       id="edit-task-title-input"
                       value="{{$current_task ? $current_task->title : ''}}"
                       title="{{$current_task ? $current_task->title : ''}}"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 description-icon"></div>
            <div class="col-md-11">
                <div class="for-textarea" id="task-description-block">
                    <div id="quill-editor"></div>
                    <div id="quill-toolbar">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-strike"></button>
                        <button class="ql-emoji"></button>
                    </div>
                    <div id="description-preview"></div>
                </div>
                <textarea name="text"
                          id="quill-editor-target-input"
                          style="display: none"
                >{!! $current_task != null ? $current_task->text : '' !!}</textarea>
                <script>
                  $(document).ready(function () {
                    $('#quill-editor .ql-editor').html($('#quill-editor-target-input').val());
                    $('#description-preview').html(findLinks($('#quill-editor-target-input').val())).show();
                    $('#task-description-block .ql-toolbar').hide();
                    $('#task-description-block .ql-container').hide();
                  });
                </script>
            </div>
        </div>
        <div class="divider2"></div>
        <input type="submit"
               class="btn btn-primary"
               name="submit"
               value="Сохранить"
               id="edit-task-submit"
        >
        <div class="row">
            <div class="col-md-12">
                <div class="tags-block">
                    @if($current_task)
                        @foreach($current_task->tags as $tag)
                            <div class="tags-item task-tag"
                                 id="task-tag-{{$tag->id}}"
                                 data-id="{{$tag->id}}"
                                 style="background-color: {{$tag->color}}"
                            >
                                <a href="{{route('list', [
                                    'workspace_id' => is_object($current_workspace) ? $current_workspace->id : $current_user->workspace->id,
                                    'user_id' => $current_user->id,
                                    'project_id' => 0,
                                    'filter_type' => 6,
                                    'post_id' => $tag->id
                                    ])}}"
                                ><i class="fa fa-tag" aria-hidden="true"></i> {{$tag->title}}</a>
                                <span class="detach-tag crm-button" data-id="{{$tag->id}}">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="subtasks-block" id="subtasks-block">
                    @if($current_task != null && count($current_task->subtasks) > 0)
                        <div class="subtasks-filter-block">
                            <div class="float-right crm-button" id="subtasks-filter-button">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                            </div>
                            <div class="dialog subtasks-filter-dialog">
                                <ul>
                                    <li>
                                        <label>
                                            <input type="radio"
                                                   name="subtasks-filter-radio"
                                                   value="1" checked
                                                   id="subtasks-filter-radio-1"
                                            >
                                            Незавершенные задачи
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio"
                                                   name="subtasks-filter-radio"
                                                   value="2"
                                                   id="subtasks-filter-radio-2"
                                            >
                                            Завершенные задачи
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" name="subtasks-filter-radio" value="3">
                                            Все задачи
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @foreach($current_task->subtasks as $task)
                            <div class="subtasks-item task-item subtask-status-{{$task->status}}"
                                 id="task-item-{{$task->id}}"
                                 style="{{$task->status == 3 ? 'display: none' : ''}}"
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
                                    <a href="#"
                                       class="task-link"
                                       data-id="{{$task->id}}"
                                       id="task-link-{{$task->id}}"
                                    >
                                        {{$task->title}}
                                    </a>
                                </span>
                                <a class="float-right task-link text-right crm-button" title="Подробности и комметарии">
                                    <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>