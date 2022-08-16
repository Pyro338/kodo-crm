$(document).ready(function ($) {
  let body = $('body');

  body.on('click', '.save-user', function () {
    let user_id = $(this).attr('data-id');
    let user_role = $('#user-role-' + user_id).val();
    updateUserAjax(user_id, user_role);
  });

  body.on('click', '.delete-user', function (e) {
    e.preventDefault();
    let user_id = $(this).attr('data-id');
    if (confirm('Вы действительно хотите удалить данного пользователя?')) {
      deleteUserAjax(user_id);
    }
  });

  $('#refresh-users').click(function () {
    refreshUsersAjax();
  });

  //users page functions

  function deleteUserAjax(user_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/" + user_id,
      dataType: "json",
      type    : 'DELETE',
      success : function (data) {
        $('.loading').hide();
        $('#user-item-' + data.success.id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function refreshUsersAjax() {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/refreshUsers/",
      dataType: "json",
      success : function () {
        $('.loading').hide();
        location.reload();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function updateUserAjax(user_id, user_role) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/" + user_id,
      dataType: "json",
      type    : 'put',
      data    : {
        user_role: user_role
      },
      success : function () {
        $('.loading').hide();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }
});