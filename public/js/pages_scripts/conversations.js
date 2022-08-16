$(document).ready(function ($) {
  let body = $('body');
  let path = url.split('/');

  let users_list = {};
  getUserListAjax();

  body.on('change', '.message-attach-input', function () {
    let fd = new FormData();
    let conversation_id = $(this).attr('data-conversation_id');
    fd.append("attachment", this.files[0]);
    uploadMessageAttachAjax(fd, conversation_id);
  });

  body.on('click', '.conversation-delete-button', function () {
    let conversation_id = $(this).attr('data-id');
    if (confirm('Удалить?')) {
      deleteConversationAjax(conversation_id);
    }
  });

  body.on('click', '.conversation-edit-title-button', function () {
    let conversation_id = $(this).attr('data-id');
    $('#conversation-title-' + conversation_id).hide(300);
    $('#conversation-title-input-' + conversation_id).show(300).focus();
  });

  body.on('click', '.message-submit', function () {
    let conversation_id = $(this).attr('data-conversation_id');
    let attachment_id = $('#message-attachment-id-input-' + conversation_id).val();
    let message_text = $('#new-message-quill-editor-' + conversation_id + ' .ql-editor').html();
    let mentions = [];
    $('#new-message-quill-editor-' + conversation_id + ' .ql-editor .mention').each(function (index, mention) {
      mentions.push($(mention).data('id'));
    });
    if (message_text || attachment_id) {
      sendMessageAjax(message_text, attachment_id, conversation_id, mentions);
    } else {
      alert('Введите текст сообщения или добавьте изображение');
    }
  });

  body.on('click', '.dialog-text-preview', function (e) {
    if (!$(e).hasClass('crm-button')) {
      window.location.replace('/conversations/' + $(this).attr('data-workspace_id') + '/' + $(this).attr('data-id') + '/');
    }
  });

  if (document.getElementsByClassName('new-message-quill-editor').length > 0) {
    new Quill('.new-message-quill-editor', {
      modules    : {
        toolbar          : '.new-message-quill-toolbar',
        mention          : {
          allowedChars          : /^[A-Za-z\sÅÄÖåäö]*$/,
          mentionDenotationChars: ["@"],
          source                : function (searchTerm, renderList, mentionChar) {
            let values = users_list;

            if (searchTerm.length === 0) {
              renderList(values, searchTerm);
            } else {
              const matches = [];
              for (i = 0; i < values.length; i++)
                if (~values[i].value.toLowerCase().indexOf(searchTerm.toLowerCase())) matches.push(values[i]);
              renderList(matches, searchTerm);
            }
          },
        },
        "emoji-toolbar"  : true,
        "emoji-shortname": true
      },
      placeholder: 'Введите сообщение...',
      theme      : 'snow',
    });
  }

  body.on('click', '.reply-button', function () {
    let id = $(this).attr('data-id');
    let text = '>> ' + $('#message-item-' + id + ' .message-body').text().trim() + '\n';
    new_message_quill.insertText(0, text);
  });

  body.on('click', '.delete-message-button', function () {
    let message_id = $(this).attr('data-id');
    if (confirm('Удалить сообщение?')) {
      deleteMessageAjax(message_id);
    }
  });

  $(document).keydown(function (event) {
    if (event.which == 13 && event.ctrlKey) {
      let conversation_id = $('.message-submit').attr('data-conversation_id');
      let attachment_id = $('#message-attachment-id-input-' + conversation_id).val();
      let message_text = $('#new-message-quill-editor-' + conversation_id + ' .ql-editor').html();
      let mentions = [];
      $('#new-message-quill-editor-' + conversation_id + ' .ql-editor .mention').each(function (index, mention) {
        mentions.push($(mention).data('id'));
      });
      if (message_text || attachment_id) {
        sendMessageAjax(message_text, attachment_id, conversation_id, mentions);
      }
    }
  });

  $('.new-message-quill-editor').click(function () {
    $('.new-message-quill-toolbar').show();
    $(this).removeClass('new-message-quill-editor-small');
  });

  body.mouseup(function (e) {
    let container = $(".panel-footer");
    if (container.has(e.target).length == 0) {
      $('.new-message-quill-toolbar').hide();
      $('.new-message-quill-editor').addClass('new-message-quill-editor-small');
    }
  });

  //conversations page functions

  function deleteConversationAjax(conversation_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/conversations/" + conversation_id,
      dataType: "json",
      type    : 'DELETE',
      success : function (data) {
        $('.loading').hide();
        if (path[3]) {
          window.location.replace('/conversations/' + data.success.workspace_id + '/');
        } else {
          $('#dialog-item-' + conversation_id).detach();
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function deleteMessageAjax(message_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/messages/" + message_id,
      dataType: "json",
      type    : 'DELETE',
      success : function (data) {
        $('.loading').hide();
        $('#message-item-' + data.success.id + ' .message-body').html(data.success.text);
        $('#message-item-' + data.success.id + ' .message-attachment').detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function sendMessageAjax(message_text, attachment_id, conversation_id, mentions) {
    ajaxSetup();
    $.ajax({
      url     : "/api/messages/",
      dataType: "json",
      type    : 'POST',
      data    : {
        text           : message_text,
        attachment_id  : attachment_id,
        conversation_id: conversation_id,
        mentions       : mentions
      },
      success : function () {
        $('#new-message-' + conversation_id).val('');
        $('#message-attachment-block-' + conversation_id).empty();
        $('#new-message-quill-editor-' + conversation_id + ' .ql-editor').empty();
        $('#message-attachment-id-input-' + conversation_id).val('');
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function uploadMessageAttachAjax(fd, conversation_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url        : "/api/messages/attach/",
      type       : "POST",
      data       : fd,
      processData: false,
      contentType: false,
      success    : function (data) {
        $('.loading').hide();
        $('#message-attachment-id-input-' + conversation_id).val(data.success.id);
        $('#message-attachment-block-' + conversation_id).empty().append('' +
          '<img class="img-small-preview" src="/download/' + data.success.id + '" style="max-width: 50%; margin-top: 10px;" ' +
          'data-url="/download/' + data.success.id + '">'
        );
      },
      error      : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }
});

function getUserListAjax() {
  ajaxSetup();
  $.ajax({
    url     : "/api/users/",
    dataType: "json",
    success : function (data) {
      users_list = data.success;
    },
    error   : function (jqxhr, status, errorMsg) {
      ajaxErrorsHandling(jqxhr, status, errorMsg);
    }
  });
}