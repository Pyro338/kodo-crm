<div class="dialog edit-project-dialog popup-small" id="edit-project-dialog">
    <input type="hidden" id="edit-project-id" value="{{$project->id}}">
    <input type="hidden" id="edit-project-workspace_id" value="{{$project->workspace_id}}">
    <input type="hidden" id="owner_id" value="{{$project->owner_id}}">
    <h3>Название*</h3>
    <input type="text" class="form-control" id="edit-project-title" required value="{{$project->title}}">
    <h3>Краткое название</h3>
    <input type="text" class="form-control" id="edit-project-alias" value="{{$project->alias}}">
    <h3>Описание</h3>
    <div class="for-textarea" id="edit-project-text-block">
        <div id="edit-project-text-quill-editor"></div>
        <div id="edit-project-text-quill-toolbar">
            <button class="ql-bold"></button>
            <button class="ql-italic"></button>
            <button class="ql-underline"></button>
            <button class="ql-strike"></button>
            <button class="ql-emoji"></button>
        </div>
    </div>
    <div class="divider2"></div>
    <div class="btn btn-primary" id="edit-project-submit">
        Сохранить
    </div>
    <div class="edit-project-close" id="edit-project-close"><i class="fa fa-times" aria-hidden="true"></i></div>
</div>