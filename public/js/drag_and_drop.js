let token = $('meta[name="csrf-token"]').attr('content');
let task_id = $('#edit-task-id-input').val();
let workspace_id = $('#edit-task-workspace-input').val();
Dropzone.autoDiscover = false;
$(document).ready(function ($) {
  $("#upload").dropzone({
    maxFiles     : 2000,
    url          : '/api/files/',
    type         : 'POST',
    paramName    : "attachments",
    acceptedFiles: "image/*",
    maxFilesize  : 5,
    init         : function () {
      this
        .on('sending', function (data, xhr, formData) {
          task_id = $('#edit-task-id-input').val();
          workspace_id = $('#edit-task-workspace-input').val();
          formData.append('_token', token);
          formData.append('task_id', task_id);
          formData.append('workspace_id', workspace_id);
        })
        .on("success", function (file, data) {
          let preview_string = '';
          let full_preview_string = '';
          if (data.success.type == 'jpeg' || data.success.type == 'jpg' || data.success.type == 'png'
            || data.success.type == 'gif') {
            preview_string = '' +
              '<div class="text-center">' +
              '  <img src="/download/' + data.success.id + '" ' +
              '     class="' + data.success.class + '" ' +
              '     data-url="/download/' + data.success.id + '"' +
              '     alt="' + data.success.alt + '"' +
              '     title="' + data.success.alt + '"' +
              '  >' +
              '</div>';
            full_preview_string = '' +
              '<div class="img-preview"' +
              '     style="background-image: url(\'/download/' + data.success.id + '\')"' +
              '     data-url="/download/' + data.success.id + '"' +
              '>' +
              '</div>';
          }
          let like_title = data.success.is_liked ? 'Убрать из избранного' : 'Добавить в избранное';
          let like_class = data.success.is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up';
          $('.edit-tasks-files').append('' +
            '<div class="file-like-button float-right crm-button like-button"' +
            '     id="file-like-button-' + data.success.id + '"' +
            '     data-id="' + data.success.id + '"' +
            '     data-type="file"' +
            '     title="' + like_title + '"' +
            '>' +
            '  <input type="hidden" ' +
            '         id="file-likes-count-input-' + data.success.id + '" ' +
            '         value="' + data.success.likes_count + '"' +
            '  >' +
            '    <i class="fa ' + like_class + '" aria-hidden="true"></i> ' +
            '    <span id="file-likes-count-' + data.success.id + '">' +
            data.success.likes_count +
            '    </span>' +
            '</div>' +
            '<div class="file-link" id="small-file-item-' + data.success.id + '">' +
            '    <a href="/download/' + data.success.id + '">' +
            '      <img class="fileicon" src="/img/fileicons/' + data.success.type + '.png"> '
            + data.success.original_filename +
            '    </a>' +
            '    <span class="file-delete" data-id="' + data.success.id + '">' +
            '      <i class="fa fa-trash" aria-hidden="true"></i>' +
            '    </span>' +
            preview_string +
            '</div>'
          );
          let task_name_string = '<span class="empty-title">Без названия</span>'
          if (data.success.task_name != null) {
            task_name_string = data.success.task_name
          }
          $('.files-list').prepend('' +
            '<div class="file-item" id="file-item-' + data.success.id + '">' +
            ' <div class="file-list-author">' +
            '  <a href="/list/' + data.success.workspace_id + '/' + data.success.author + '/0/0" ' +
            '     class="users-item" ' +
            '     title="' + data.success.username + '"' +
            '     style="background-color: ' + data.success.color + '; ' +
            '            background-image: ' + data.success.background_image + '"' +
            '     data-user_fullname="' + data.success.fullname + '"' +
            '     data-user_department="' + data.success.department + '"' +
            '     data-user_office="' + data.success.office + '">' +
            data.success.first_letters +
            '  </a>' +
            ' </div>' +
            ' <div class="file-list-name">' +
            full_preview_string +
            '  <a href="/download/' + data.success.id + '">' +
            '   <img class="fileicon" src="/img/fileicons/' + data.success.type + '.png" alt="' + data.success.type + '"> ' +
            data.success.original_filename +
            '  </a>' +
            '  <span class="file-delete" data-id="' + data.success.id + '">' +
            '    <i class="fa fa-trash" aria-hidden="true"></i>' +
            '  </span>' +
            ' </div>' +
            ' <div class="file-list-task">' +
            '  Задача:' +
            '  <a href="/list/' + data.success.workspace_id + '/' + data.success.author + '/0/0/' + data.success.task_id + '" ' +
            '     class="task-link" ' +
            '     data-id="' + data.success.task_id + '"' +
            '  >' +
            task_name_string +
            '  </a>' +
            ' </div>' +
            ' <hr>' +
            '</div>');
          $('.no-files-label').detach();
          $('#task-attach-popup').hide(300);
          $('.dz-message').show().html('<span>Перетащите картинку сюда</span>');
          $('.dz-preview').hide();
        });
    }
  });
});