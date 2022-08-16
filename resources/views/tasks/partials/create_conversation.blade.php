<input type="hidden" id="current-user" value="{{Auth::user()->id}}">
<input type="text" class="form-control" placeholder="Тема обсуждения" id="new-conversation-title">
<input type="hidden" id="new-conversation-attachment-id-input">
<input type="file" id="new-conversation-attach-input" accept="image/*" style="display: none">
<div class="for-textarea" id="new-conversation-block">
    <div id="new-conversation-quill-editor"></div>
    <div id="new-conversation-quill-toolbar">
        <button class="ql-bold"></button>
        <button class="ql-italic"></button>
        <button class="ql-underline"></button>
        <button class="ql-strike"></button>
        <button class="ql-emoji"></button>
    </div>
</div>
<div class="divider2"></div>
<div id="new-conversation-attachment-block"></div>
<button class="btn btn-primary" id="new-conversation-submit">Отправить</button>
<div class="float-right conversation-button" id="new-conversation-attach">
    <label for="new-conversation-attach-input"><i class="fa fa-paperclip" aria-hidden="true"></i></label>
</div>