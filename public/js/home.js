$(function () {
  var url = window.location.href.split('/');
  var page = url[url.length - 1];
  var body = $('body');

  body.on('mouseenter', '#nav-sectors-link', function () {
    if (page != 'sectors') {
      $('.task-page').hide();
      $('.sectors-page').show();
      $('.sections-page').hide();
      $('.clients-page').hide();
      $('.users-page').hide();
      $('.info-page').hide();
    } else {
      $('.global-section').hide();
      $('.task-page').show();
    }
  });

  body.on('mouseenter', '#nav-sections-link', function () {
    if (page != 'sections') {
      $('.task-page').hide();
      $('.sectors-page').hide();
      $('.sections-page').show();
      $('.clients-page').hide();
      $('.users-page').hide();
      $('.info-page').hide();
    } else {
      $('.global-section').hide();
      $('.task-page').show();
    }
  });

  body.on('mouseenter', '#nav-clients-link', function () {
    if (page != 'clients') {
      $('.task-page').hide();
      $('.sectors-page').hide();
      $('.sections-page').hide();
      $('.clients-page').show();
      $('.users-page').hide();
      $('.info-page').hide();
    } else {
      $('.global-section').hide();
      $('.task-page').show();
    }
  });

  body.on('mouseenter', '#nav-users-link', function () {
    if (page != 'users') {
      $('.task-page').hide();
      $('.sectors-page').hide();
      $('.sections-page').hide();
      $('.clients-page').hide();
      $('.users-page').show();
      $('.info-page').hide();
    } else {
      $('.global-section').hide();
      $('.task-page').show();
    }
  });

  body.on('mouseenter', '#nav-info-link', function () {
    if (page != 'info') {
      $('.task-page').hide();
      $('.sectors-page').hide();
      $('.sections-page').hide();
      $('.clients-page').hide();
      $('.users-page').hide();
      $('.info-page').show();
    } else {
      $('.global-section').hide();
      $('.task-page').show();
    }
  });

  $(document).on('click', 'a[href="#"]', function (e) {
    e.preventDefault();
  });

  //Open menu func
  $(document).on('click', '.js-open-menu-link', function () {
    var ths = $(this),
      wrap = ths.closest('.js-open-menu-wrapper');

    wrap.toggleClass('global-wrapper-open');
    if (page == '' && wrap.hasClass('global-wrapper-open')) {
      $('.task-page').hide();
    } else {
      $('.task-page').show()
    }
  });

  //Nav func
  $(document).on('click', '.js-nav-link', function () {
    var ths = $(this),
      dataSection = ths.attr('data-section'),
      wrap = ths.closest('.js-open-menu-wrapper');

    wrap.find('.current-section').removeClass('current-section');
    wrap.find('.js-nav-section[data-section=' + dataSection + ']').addClass('current-section');
    ths.addClass('current-section');
    wrap.addClass('global-wrapper-page');

    $('.js-open-menu-link').click();

    if (dataSection === 'task-section') {
      wrap.addClass('global-wrapper-task-page');
    } else {
      wrap.removeClass('global-wrapper-task-page');
    }
  });

  body.mouseup(function (e) {
    let container = $(".dialog");
    if (container.has(e.target).length == 0) {
      container.hide();
    }
  });

  body.on('click', '.user-role_name', function () {
    let user_id = $(this).attr('data-id');
    $(this).hide();
    $('#user-role-container-' + user_id).show();
  });

  body.on('click', '.user-department', function () {
    let user_id = $(this).attr('data-id');
    $(this).hide();
    $('#user-department-container-' + user_id).show();
  });

  body.on('click', '.user-office', function () {
    let user_id = $(this).attr('data-id');
    $(this).hide();
    $('#user-office-container-' + user_id).show();
  });

  body.on('focusout', '.popup-container input', function () {
    let user_id = $(this).attr('data-id');
    let key = $(this).attr('data-key')
    let value = $(this).val();
    $('#user-' + key + '-' + user_id).text(value);
    changeUserPropertyAjax(user_id, key, value);
  });

  body.on('change', '.popup-container select', function () {
    let user_id = $(this).attr('data-id');
    let key = $(this).attr('data-key')
    let value = $(this).val();
    let text = $('#user-' + key + '-' + user_id + '-' + value).text().trim();
    $('#user-' + key + '-' + user_id).text(text);
    changeUserPropertyAjax(user_id, key, value);
  });

  body.mouseup(function (e) {
    var container = $(".popup-container");
    if (container.has(e.target).length === 0) {
      container.hide();
      $('.popup-label').show();
    }
  });

  body.on('click', '#new-workspace-submit', function () {
    let title = $('#new-workspace-title').val();
    let description = $('#new-workspace-description').val();
    if (!title || title.trim() == '') {
      alert('Введите название отдела');
    } else {
      createWorkspaceAjax(title, description);
    }
  });

  body.on('click', '.delete-workspace', function () {
    let id = $(this).attr('data-id');
    if (confirm('Удалить?')) {
      deleteWorkspaceAjax(id);
    }
  });

  body.on('click', '.edit-workspace', function () {
    let id = $(this).attr('data-id');
    $('#workspace-item-' + id).hide();
    $('#workspace-item-edit-' + id).show();
  });

  body.on('click', '.save-workspace', function () {
    let id = $(this).attr('data-id');
    let title = $('#edit-workspace-title-' + id).val();
    let description = $('#edit-workspace-description-' + id).val();
    if (!title || title.trim() == '') {
      alert('Введите название отдела');
    } else {
      updateWorkspaceAjax(id, title, description);
    }
  });

  body.on('click', '.user-access', function () {
    let id = $(this).attr('data-id');
    $('#user-access-dialog-' + id).show(300);
  });

  body.on('click', '.user-access-item', function () {
    let user_id = $(this).attr('data-user_id');
    let workspace_id = $(this).attr('data-workspace_id');
    if (!$(this).hasClass('disabled')) {
      toggleWorkspaceAccessAjax(user_id, workspace_id);
    }
  });
});

//AJAX SETUP
function ajaxErrorsHandling(jqxhr, status, errorMsg) {
  $('.loading').hide();
  if (jqxhr.status == 401 || jqxhr.status == 403 || jqxhr.status == 419) {
    let url = document.location.pathname;
    let path = url.split('/');
    if (path[1] != 'login') {
      $(location).attr('href', '/login/');
    } else {
      console.log(errorMsg);
    }
  } else {
    console.log(errorMsg);
  }
}

function ajaxSetup() {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN'                    : $('meta[name="csrf-token"]').attr('content'),
      'Access-Control-Allow-Credentials': true
    }
  });
}

function checkEmailUnique(email) {
  ajaxSetup();
  $.ajax({
    url     : "/api/users/checkEmailUnique/",
    dataType: "json",
    type    : 'POST',
    data    : {
      email: email
    },
    success : function (data) {
      if (data.success.unique) {
        return true;
      } else {
        return false;
      }
    }
  });
}

function createUserAjax() {
  let msg = $('#user-create-form').serialize();
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : "/api/users/",
    dataType: "json",
    type    : 'POST',
    data    : msg,
    success : function (data) {
      $('.loading').hide();
      $('.card-body').prepend('<div class="alert alert-success">' + data.success.message + '</div>');
      $('.form-control').val('');
    }
  });
}

function createWorkspaceAjax(title, description) {
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : "/api/workspaces/",
    dataType: "json",
    type    : 'POST',
    data    : {
      title      : title,
      description: description
    },
    success : function (data) {
      $('.loading').hide();
      let description_string = data.success.description ? data.success.description : '';
      $('#workspaces-list').append('' +
        '<tr id="workspace-item-' + data.success.id + '">' +
        '  <td class="text-left">' +
        '    <span id="workspace-title-' + data.success.id + '">' +
        data.success.title +
        '    </span>' +
        '  </td>' +
        '  <td>' +
        '    <span id="workspace-description-' + data.success.id + '">' +
        description_string +
        '    </span>' +
        '  </td>' +
        '  <td class="text-right">' +
        '    <span id="edit-workspace-' + data.success.id + '" class="icon-button edit-workspace" data-id="' + data.success.id + '">' +
        '      <i class="fa fa-pencil" aria-hidden="true"></i>' +
        '    </span>' +
        '    <span id="delete-workspace-' + data.success.id + '" class="icon-button delete-workspace" data-id="' + data.success.id + '">' +
        '      <i class="fa fa-trash" aria-hidden="true"></i>' +
        '     </span>\n' +
        '   </td>' +
        '</tr>' +
        '<tr id="workspace-item-edit-' + data.success.id + '"' +
        '    style="display: none"' +
        '    class="workspace-item-edit"' +
        '>' +
        '  <td class="text-left">' +
        '    <input type="text"' +
        '           id="edit-workspace-title-' + data.success.id + '"' +
        '           value="' + data.success.title + '"' +
        '    >' +
        '  </td>' +
        '  <td>' +
        '    <input type="text"' +
        '           id="edit-workspace-description-' + data.success.id + '"' +
        '           value="' + description_string + '"' +
        '    >' +
        '  </td>' +
        '  <td class="text-right">' +
        '    <span id="edit-workspace-save"' +
        '          class="icon-button save-workspace"' +
        '          data-id="' + data.success.id + '"\n' +
        '    >' +
        '      <i class="fa fa-floppy-o" aria-hidden="true"></i>' +
        '    </span>\n' +
        '  </td>' +
        '</tr>'
      );
      $('#empty-workspaces-row').detach();
      $('#new-workspace-title').val('');
      $('#new-workspace-description').val('');

      $('.user-access-dialog ul').each(function (index, item) {
        let user_id = $(item).attr('data-user_id');
        $(item).append('' +
          '<li class="user-access-item"' +
          '    id="user-access-item-' + user_id + '-' + data.success.id + '"' +
          '    data-user_id="' + user_id + '"' +
          '    data-workspace_id="' + data.success.id + '"' +
          '>' +
          '  <span class="access-toggle-button"' +
          '        id="access-toggle-button-' + user_id + '-' + data.success.id + '"' +
          '        data-user_id="' + user_id + '"' +
          '        data-workspace_id="' + data.success.id + '"' +
          '  >' +
          '    <i class="fa fa-plus-square-o"></i>' +
          '  </span>' +
          data.success.title +
          '</li>' +
          '');
      });
    }
  });
}

function changeUserPropertyAjax(user_id, key, value) {
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : '/api/users/' + user_id + '/changeProperty/',
    dataType: "json",
    type    : 'PUT',
    data    : {
      key  : key,
      value: value
    },
    success : function () {
      $('.loading').hide();
      $(".popup-container").hide();
      $('.popup-label').show();
      if (key == 'department') {
        $('#user-access-dialog-' + user_id + ' .user-access-item').removeClass('disabled');
        $('#user-access-item-' + user_id + '-' + value).addClass('disabled');
      }
    }
  });
}

function toggleWorkspaceAccessAjax(user_id, workspace_id) {
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : '/api/users/' + user_id + '/toggleWorkspaceAccess/',
    dataType: "json",
    type    : 'PUT',
    data    : {
      workspace_id: workspace_id
    },
    success : function (data) {
      $('.loading').hide();
      $('#user-access-dialog-' + user_id + ' i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
      let workspaces_ids = JSON.parse(data.success.workspaces);
      for (var i = 0; i < workspaces_ids.length; i++) {
        if (workspaces_ids[i]) {
          $('#access-toggle-button-' + user_id + '-' + workspaces_ids[i] + ' i')
            .addClass('fa-minus-square-o')
            .removeClass('fa-plus-square-o');
        }
      }
    }
  });
}

function updateWorkspaceAjax(id, title, description) {
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : "/api/workspaces/" + id,
    dataType: "json",
    type    : 'PUT',
    data    : {
      id         : id,
      title      : title,
      description: description
    },
    success : function (data) {
      $('.loading').hide();
      $('#workspace-title-' + data.success.id).text(data.success.title);
      $('#workspace-description-' + data.success.id).text(data.success.description);
      $('#workspace-item-' + data.success.id).show();
      $('#workspace-item-edit-' + data.success.id).hide();
    }
  });
}

function deleteWorkspaceAjax(id) {
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : "/api/workspaces/" + id,
    dataType: "json",
    type    : 'DELETE',
    data    : {
      id: id
    },
    success : function (data) {
      $('.loading').hide();
      $('#workspace-item-' + data.success.id).detach();
    }
  });
}