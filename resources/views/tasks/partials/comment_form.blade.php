<form class="form-horisontal" method="post" id="create-comment-form">
    {{csrf_field()}}
    <input type="hidden" name="type" value="comment">
    <input type="hidden" name="task_id" value="@if($current_task != null){{$current_task->id}}@endif" id="edit-task-add-comment">
    <input type="hidden" name="author_id" value="{{Auth::user()->id}}">
    <input type="hidden"
           name="recipient_id"
           value="@if($current_task != null){{$current_task->implementer}}@endif"
           id="add-comment-recipient_id"
    >
    <input type="hidden" name="mentions" id="edit-task-comment-mentions">
    <div class="for-textarea" id="edit-task-comment-block">
        <div id="edit-task-comment-quill-editor"></div>
        <div id="edit-task-comment-quill-toolbar">
            <button class="ql-bold"></button>
            <button class="ql-italic"></button>
            <button class="ql-underline"></button>
            <button class="ql-strike"></button>
            <button class="ql-emoji"></button>
        </div>
    </div>
    <textarea name="text"
              id="edit-task-comment-input"
              style="display: none"
    ></textarea>
    <div class="divider2"></div>
    <button class="btn btn-primary" id="edit-task-comment-submit">Отправить</button>
</form>