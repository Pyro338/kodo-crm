<input type="hidden" id="message-attachment-id-input-{{$conversation->id}}">
<input type="file"
       id="message-attach-input-{{$conversation->id}}"
       accept="image/*" style="display: none"
       class="message-attach-input"
       data-conversation_id="{{$conversation->id}}"
>
<div id="foo"></div>
<div id="message-attachment-block-{{$conversation->id}}" class="message-attachment-block"></div>
<div id="new-message-quill-editor-{{$conversation->id}}" class="new-message-quill-editor new-message-quill-editor-small"></div>
<div id="new-message-quill-toolbar-{{$conversation->id}}" class="new-message-quill-toolbar">
    <button class="ql-bold"></button>
    <button class="ql-italic"></button>
    <button class="ql-underline"></button>
    <button class="ql-strike"></button>
    <button class="ql-emoji"></button>

    <button class="float-right conversation-button message-submit" id="message-submit-{{$conversation->id}}" data-conversation_id="{{$conversation->id}}">
        <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
    </button>
    <button class="float-right conversation-button" id="message-attach-{{$conversation->id}}" data-conversation_id="{{$conversation->id}}">
        <label for="message-attach-input-{{$conversation->id}}"><i class="fa fa-paperclip" aria-hidden="true"></i></label>
    </button>
</div>