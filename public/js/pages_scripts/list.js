$(document).ready(function ($) {
  let body = $('body');

  $('.restore-task-button').click(function () {
    let task_id = $(this).attr('data-id');
    restoreTaskAjax(task_id);
  });

  body.on('change', '.asignee-select', function () {
    let task_id = $(this).attr('data-id');
    let user_id = $(this).val();
    asigneeAjax(task_id, user_id);
  });

  body.on('click', '.time-mark', function () {
    let task_id = $(this).attr('data-id');
    let time_mark = $(this).attr('data-mark');
    changeTimeMarkAjax(task_id, time_mark);
  });

  //list page functions

  function asigneeAjax(task_id, user_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tasks/" + task_id + '/assign/',
      dataType: "json",
      type    : 'PUT',
      data    : {
        user_id: user_id
      },
      success : function (data) {
        $('.loading').hide();
        $('#asignee-dialog-' + data.success.id).hide();
        $('#asignee-button-' + data.success.id)
          .text(data.success.user.first_letters)
          .attr('data-user_id', data.success.user.id)
          .attr('data-user_fullname', data.success.user.fullname)
          .attr('data-user_department', data.success.user.department)
          .attr('data-user_office', data.success.user.office)
          .css('backgroundColor', data.success.user.color)
          .css('backgroundImage', data.success.user.background_image);
        $('#asignee-user-link-' + data.success.id).html('' +
          '<a href="/list/' + data.success.workspace_id + '/' + data.success.delegated_id + '/0/0">' +
          '  Посмотреть задачи пользователя ' + data.success.user.name +
          '</a>'
        );
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function changeTimeMarkAjax(task_id, time_mark) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/tasks/' + task_id + '/changeTimeMark/',
      dataType: "json",
      type: 'PUT',
      data    : {
        time_mark: time_mark
      },
      success : function (data) {
        $('.loading').hide();
        $('#mark-dialog-' + task_id).hide(300);
        $('#task-item-' + task_id).removeClass('time-mark-new').removeClass('time-mark-today')
          .removeClass('time-mark-later').removeClass('time-mark-upcoming').addClass('time-mark-' + data.success);
        $('.' + time_mark + '-tasks-body').append($('#task-item-' + task_id).detach());
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function restoreTaskAjax(task_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tasks/" + task_id + '/restore/',
      dataType: "json",
      type    : 'PUT',
      success : function () {
        $('.loading').hide();
        $('#task-item-' + task_id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }
});