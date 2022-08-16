<form method="post" class="form-horisontal" id="create-task-form">
    {{csrf_field()}}
    <input type="hidden"
           name="current_user_id"
           value="{{$current_user->id}}"
    >
    <input type="hidden"
           name="project_id"
           id="create-task-form-project_id-input"
    >
    <input type="hidden"
           name="status"
           value="1"
           id="create-task-form-status-input"
    >
    <input type="hidden"
           name="workspace_id"
           value="{{$current_user->workspace_id}}"
           id="create-task-form-workspace_id-input"
    >
    <input type="hidden"
           name="owner_id"
           value="{{$current_user->id}}"
           id="create-task-form-owner_id-input"
    >
    <input type="hidden"
           name="attachment_ids"
           id="create-task-form-attachment_ids-input"
    >
    <input type="file"
           id="create-task-form-attach-input"
           accept="image/gif,image/jpeg,image/png,application/zip,application/x-rar-compressed,application/x-7z-compressed,application/pdf,application/msword,application/rtf,application/vnd.ms-excel,text/plain,application/json,"
           style="display: none"
           name="files[]"
           data-multiple-caption="{count} files selected"
           multiple
    >
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6">
                <select name="delegated_id" class="form-control" id="create-task-form-delegated_id-input">
                    <option value="{{$current_user->id}}" selected>
                        {{$current_user->name}}
                    </option>
                    @foreach($users as $user)
                        @if($user->id != $current_user->id)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <span class="float-right crm-button" title="Закрыть" id="close-create-task-form">
                     <i class="fa fa-times" aria-hidden="true"></i>
                </span>
                <span class="float-right crm-button" id="create-task-form-attach-button" title="Добавить файл">
                    <label for="create-task-form-attach-input">
                        <i class="fa fa-file-o" aria-hidden="true" style="cursor: pointer"></i>
                    </label>
                </span>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div class="project-to-new-task">
                    <span>
                        Добавить к проекту
                    </span>
                    <div class="dialog project-to-new-task-dialog">
                        <ul>
                            @foreach($projects as $project)
                                @if((is_object($current_workspace) && $project->workspace_id == $current_workspace->id)
                                || (!is_object($current_workspace) && $project->workspace_id == $current_user->workspace->id))
                                    <li class="project-to-new-task-dialog-item"
                                        data-id="{{$project->id}}"
                                    >
                                        {{$project->title}}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <input type="text"
                       id="new-task-due-date-input"
                       name="due_date"
                       class="datepicker"
                       placeholder="Назначить дату завершения"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-11">
                <input type="text" name="title" class="form-control" id="create-task-form-title-input">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 description-icon">
                <i class="fa fa-align-left" aria-hidden="true"></i>
            </div>
            <div class="col-md-11">
                <div class="for-textarea" id="create-task-description-block">
                    <div id="create-quill-editor"></div>
                    <div id="create-quill-toolbar">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-strike"></button>
                        <button class="ql-emoji"></button>
                    </div>
                    <div id="create-description-preview"></div>
                </div>
                <textarea name="text"
                          id="create-quill-editor-target-input"
                          style="display: none"
                ></textarea>
                <script>
                  $(document).ready(function(){
                    $('#create-task-description-block .ql-toolbar').hide();
                    $('#create-task-description-block .ql-container').hide();
                  });
                </script>
            </div>
        </div>
        <div class="divider"></div>
        <input type="submit" class="btn btn-primary" name="submit" value="Создать" id="create-task-form-submit">
        <div class="divider"></div>
        <table id="create-tasks-attachments-block">
            <tbody></tbody>
        </table>
    </div>
</form>