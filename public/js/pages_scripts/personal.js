$(document).ready(function ($) {
  function ajaxSetup() {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN'                    : $('meta[name="csrf-token"]').attr('content'),
        'Access-Control-Allow-Credentials': true
      }
    });
  }

  let body = $('body');

  body.on('change', '#avatar-input', function () {
    let fd = new FormData();
    fd.append("attachment", this.files[0]);
    if (this.files[0].size > 12582912) {
      alert('Размер файла не должен превышать 12 мегабайт!')
    } else {
      uploadAvatarAjax(fd);
    }
  });

  body.on('change', '#conversation-subscribe-radio', function () {
    toggleConversationSubscribeAjax();
  });

  body.on('change', '#enable-sound-radio', function () {
    toggleEnableSoundAjax();
  });

  body.on('change', '#subscribe-radio', function () {
    toggleSubscribeAjax();
  });

  body.on('click', '#avatar-delete-button', function () {
    if (confirm('Удалить?')) {
      deleteAvatarAjax();
    }
  });

  body.on('click', '#password-submit', function () {
    let validate = true;
    let old_password = $('#old-password').val();
    let new_password = $('#new-password').val();
    let confirm_password = $('#confirm-password').val();
    if (!old_password || old_password.trim() == '') {
      validate = false;
      alert('Введите старый пароль!');
    }
    if (!new_password || new_password.trim() == '') {
      validate = false;
      alert('Введите новый пароль!');
    }
    if (new_password.length < 6) {
      validate = false;
      alert('Пароль дожен быть не короче 6 символов');
    }
    if (!confirm_password || confirm_password.trim() == '') {
      validate = false;
      alert('Введите подтверждение пароля!');
    }
    if (new_password != confirm_password) {
      validate = false;
      alert('Вы ввели неверное подтверждение пароля!');
    }
    if (validate) {
      changePasswordAjax(new_password);
    }
  });

  body.on('click', '#user-update-submit', function (e) {
    e.preventDefault();
    let id = $('#user-update-id').val();
    let name = $('#user-update-name').val();
    let last_name = $('#user-update-last_name').val();
    let first_name = $('#user-update-first_name').val();
    let middle_name = $('#user-update-middle_name').val();
    let email = $('#user-update-email').val();
    let phone = $('#user-update-phone').val();
    let about = $('#user-update-about').val();
    let validate = true;
    if (!name || $('#user-update-name').val().trim() == '') {
      alert('Поле Nickname обязательное для заполнения');
      validate = false;
    }
    if (!email || $('#user-update-email').val().trim() == '') {
      alert('Поле Email обязательное для заполнения');
      validate = false;
    }
    if (checkEmailUnique(email) == false) {
      alert('Пользователь с таким адресом электронной почты уже существует в системе!');
      validate = false;
    }
    if (validate) {
      updateUserPersonalAjax(id, name, last_name, first_name, middle_name, email, phone, about);
    }
  });

  //personal page functions

  function changePasswordAjax(password) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/changePassword/",
      dataType: "json",
      type: 'POST',
      data    : {
        password: password
      },
      success : function (data) {
        $('.loading').hide();
        if (data.success.password_changed) {
          alert('Пароль успешно изменен!');
          $('#old-password').val('');
          $('#new-password').val('');
          $('#confirm-password').val('');
        } else {
          alert('Вы ввели неверный старый пароль!');
          $('#old-password').val('');
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
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
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function deleteAvatarAjax() {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/deleteAvatar/",
      dataType: "json",
      type    : 'POST',
      success : function () {
        $('.loading').hide();
        $('#avatar-block').css('backgroundImage', 'url(/img/no-photo.png)');
        $('#avatar-delete-button').detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleConversationSubscribeAjax() {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/toggleConversationSubscribe/",
      dataType: "json",
      type    : 'POST',
      success : function () {
        $('.loading').hide();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleEnableSoundAjax() {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/toggleEnableSound/",
      dataType: "json",
      type    : 'POST',
      success : function () {
        $('.loading').hide();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleSubscribeAjax() {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/users/toggleSubscribe/",
      dataType: "json",
      type    : 'POST',
      success : function () {
        $('.loading').hide();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function updateUserPersonalAjax(id, name, last_name, first_name, middle_name, email, phone, about) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/users/' + id + '/updatePersonal/',
      dataType: "json",
      type    : 'PUT',
      data    : {
        id         : id,
        name       : name,
        last_name  : last_name,
        first_name : first_name,
        middle_name: middle_name,
        email      : email,
        phone      : phone,
        about      : about
      },
      success : function () {
        $('.loading').hide();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function uploadAvatarAjax(fd) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url        : "/api/users/uploadAvatar/",
      type       : "POST",
      data       : fd,
      processData: false,
      contentType: false,
      success    : function (data) {
        $('.loading').hide();
        $('#avatar-block').css('backgroundImage', 'url(/download/' + data.success.id + ')');
        $('#avatar-delete-button').detach();
        $('.avatar-body').append('<div class="btn btn-danger" id="avatar-delete-button">Удалить</div>');
      },
      error      : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }
});