let url = document.location.pathname;
let path = url.split('/');
let visibly_filter = 1;
let task_in_progress = 0;
let active_notification = 0;
let notification_title = '';
let document_title = $(document).attr('title');
let cursorX = 0;
let cursorY = 0;
let access_users_array = [];
let sidebar_opened = $(window).width() > 1600 ? true : false;

$(window).load(function () {
  if ($(".message-block").length > 0) {
    $(".message-block").scrollTop($(".message-item:last-child").position().top);
  }
});

$(document).mousemove(function (e) {
  cursorX = e.pageX; // положения по оси X
  cursorY = e.pageY; // положения по оси Y
});

$(document).ready(function ($) {
  function ajaxSetup() {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN'                    : $('meta[name="csrf-token"]').attr('content'),
        'Access-Control-Allow-Credentials': true
      }
    });
  }

  let current_user_id = $('#current-user').val();
  let current_workspace_id = $('#current-workspace').val();

  let my_tasks_button_text = 'Мои задачи';
  switch (path[1]) {
    case 'projects':
      my_tasks_button_text = 'Проекты';
      break;
    case 'conversations':
      my_tasks_button_text = 'Обсуждения';
      break;
  }
  $('.my-tasks-button-text').text(my_tasks_button_text);

  /*construct page title*/
  active_notification = $('#unread-messages').val();
  let notification_bullets = '';
  for (var i = 0; i < active_notification; i++) {
    notification_bullets = notification_bullets + '• ';
  }
  $(document).attr('title', notification_bullets + document_title);
  notification_title = localStorage.getItem('notification_title');
  $('.notification-bell-tooltip').html(notification_title);

  let ip = '192.168.10.10';
  if ($('#ip').val() !== undefined) {
    ip = $('#ip').val();
  }

  let conn = new ab.connect(
    'ws://' + ip + ':8080',
    function (session) {
      session.subscribe('onNewData', function (topic, data) {
        notification(data.data);
      });
    },

    function (code, reason, detail) {
      console.warn('Websocket connection closed! Code = ' + code + '; Reason = ' + reason + '; Detail = ' + detail + ';');
      restartPushServerAjax();
    },

    {
      'maxRetries'          : 60,
      'retryDelay'          : 4000,
      'skipSubprotocolCheck': true
    }
  );
  let body = $('body');

  $('#panel1').show(300);
  sortTasksItems();
  titleToEmptyLinks();

  $('.tasks-body').sortable({
    handle     : '.drag-button',
    placeholder: "ui-sortable-placeholder",
    stop       : function () {
      data = $('.tasks-body input').serialize();
      saveOrderAjax(data);
    }
  });

  $('.subtasks-block').sortable({
    handle     : '.drag-button',
    placeholder: "ui-sortable-placeholder",
    stop       : function () {
      data = $('.subtasks-block input').serialize();
      saveOrderAjax(data);
    }
  });

  //** INTERFACE USAGE**//

  //SCROLL BUTTONS
  $("#up").click(function () {
    //Необходимо прокрутить в начало страницы
    let curPos = $(document).scrollTop();
    let scrollTime = curPos / 1.73;
    $(".main").animate({"scrollTop": 0}, scrollTime);
  });

  $("#down").click(function () {
    //Необходимо прокрутить в конец страницы
    let curPos = $(document).scrollTop();
    let height = 999999999;
    let scrollTime = (height - curPos) / 1.73;
    $(".main").scrollTop(height);
  });

  //DIALOGS
  //open hover dialog
  body.on('mouseenter', '.dialog-hover-block', function () {
    $(this).find("ul").css('left', $(this).position().left - 100).slideDown("fast");
  });

  //close hover dialog
  body.on('mouseleave', '.dialog-hover-block ul', function () {
    $(this).slideUp("fast");
  });

  //close dialogs
  body.mouseup(function (e) {
    let container = $(".dialog");
    if (container.has(e.target).length == 0) {
      container.hide();
    }
  });

  //LINKS
  //open task in right panel
  body.on('click', '.task-link', function (e) {
    e.preventDefault();
    let task_id = $(this).attr('data-id');
    task_in_progress = 0;
    getTaskAjax(task_id);
  });

  //SIDEBAR PANEL
  //close sidebar
  let conversation_panel_opened = true;
  let task_panel_opened = true;

  $('.close').click(function () {
    $('.sidebar').hide(300);
    $('.main').show().css('width', '100%');
    $('.menu-button').show();
    $('.main .header').css('width', '100%').css('left', '0');
    $('.page-header').css('width', '100%');
    $('.page-body').css('width', '100%').css('left', '0');
    $('.left-panel-conversations').css('width', '100%').css('left', '0');
    $('.left-panel').css('width', '100%').css('left', '0');
    $('.right-panel-edit').css('width', '100%');
    sidebar_opened = false;
  });

  //open sidebar
  body.on('click', '.menu-button', function () {
    $('.sidebar').show(300);
    $('.menu-button').hide();
    if ($(window).width() > 952) {
      $('.main .header').css('width', '75%').css('left', '25%');
      $('.main').css('width', '75%');
      $('.page-header').css('width', '75%');
      $('.page-body').css('width', '75%').css('left', '25%');
      if (conversation_panel_opened) {
        $('.right-panel-edit').css('width', '37%');
      } else {
        $('.right-panel-edit').css('width', '75%');
      }
      if (task_panel_opened) {
        $('.left-panel-conversations').css('width', '38%').css('left', '25%');
      } else {
        $('.left-panel-conversations').css('width', '75%').css('left', '25%');
      }
      if ($('.left-panel').hasClass('col-md-12')) {
        $('.left-panel').css('width', '75%').css('left', '25%');
      } else {
        $('.left-panel').css('width', '38%').css('left', '25%');
      }
    } else if ($(window).width() > 600) {
      $('.main').css('width', '75%');
      $('.page-header').css('width', '75%');
      $('.page-body').css('width', '100%');
      $('.left-panel-conversations').css('width', '38%');
      $('.right-panel-edit').css('width', '37%');
      $('.left-panel').css('width', '75%').css('left', '25%');
    } else {
      $('.main').hide();
    }
    sidebar_opened = true;
  });

  //toggle reports list
  $('.reports-header').click(function () {
    $('.reports-closed').toggle();
    $('.reports-opened').toggle();
    $('.reports-body').toggle();
  });

  //TOP MENU
  //my tasks menu
  $('#my-tasks-button').click(function (e) {
    e.preventDefault();
    $('.my-tasks-dialog').show();
  });

  //toggle fast create menu
  $('#fast-create-dialog-button').click(function (e) {
    e.preventDefault();
    $('.fast-create-dialog').toggle(300);
  });

  //toggle user menu
  $('#user-dialog-button').click(function (e) {
    e.preventDefault();
    $('.user-dialog').toggle(300);
  });

  //toggle workspaces menu
  $('#workspace-dialog-button').click(function (e) {
    e.preventDefault();
    $('#workspace-dialog').toggle(300);
  });

  $('.workspace-link').click(function () {
    let workspace_id = $(this).attr('data-workspace_id');
    let user_id = $('#current-user').val();
    let destination_url = '/list/' + workspace_id + '/' + user_id + '/0/0/';
    switch (path[1]) {
      case 'list':
        destination_url = '/list/' + workspace_id + '/' + path[3] + '/' + path[4] + '/' + path[5] + '/';
        break;
      case 'conversations':
        destination_url = '/conversations/' + workspace_id + '/';
        break;
      case 'projects':
        destination_url = '/projects/' + workspace_id + '/' + path[3] + '/';
        break;
    }
    window.location.replace(destination_url);
  });

  //MAIN PANEL
  //toggle tabs
  $('.page-header-submenu').click(function () {
    $('.page-header-submenu').removeClass('page-header-submenu-active');
    $(this).addClass('page-header-submenu-active');
    $('#inbox-active').toggle(300);
    $('#inbox-arhive').toggle(300);
  });

  $('#page-header-list').click(function () {
    $('#my_tasks-list').show(300);
    $('#my_tasks-calendar').hide(300);
    $('#my_tasks-files').hide(300);
  });

  $('#page-header-calendar').click(function () {
    $('#my_tasks-list').hide(300);
    $('#my_tasks-calendar').show(300);
    $('#my_tasks-files').hide(300);
  });

  $('#page-header-files').click(function () {
    $('#my_tasks-list').hide(300);
    $('#my_tasks-calendar').hide(300);
    $('#my_tasks-files').show(300);
  });

  //LEFT PANEL
  //toggle filter dialog
  $('.filter').click(function () {
    $('.filter-dialog').toggle(300);
  });

  //changing filter options
  body.on('click', '#all-radio', function () {
    $('.task-visibly-status-0').show();
    $('.task-visibly-status-1').show();
    $('.task-visibly-status-2').show();
    $('.task-visibly-status-3').show();
    $('.task-visibly-status-4').show();
    $('.task-visibly-status-5').show();
    $('.filter-dialog').hide(300);
    visibly_filter = 3;
  });

  body.on('click', '#completable-radio', function () {
    $('.task-visibly-status-0').hide();
    $('.task-visibly-status-1').hide();
    $('.task-visibly-status-2').hide();
    $('.task-visibly-status-3').show();
    $('.task-visibly-status-4').hide();
    $('.task-visibly-status-5').show();
    $('.filter-dialog').hide(300);
    visibly_filter = 2;
  });

  body.on('click', '#incompletable-radio', function () {
    $('.task-visibly-status-0').show();
    $('.task-visibly-status-1').show();
    $('.task-visibly-status-2').show();
    $('.task-visibly-status-3').hide();
    $('.task-visibly-status-4').show();
    $('.task-visibly-status-5').hide();
    $('.filter-dialog').hide(300);
    visibly_filter = 1;
  });

  //toggle time mark blocks
  $('.later-tasks-header').click(function () {
    $('.later-tasks-closed').toggle();
    $('.later-tasks-opened').toggle();
    $('.later-tasks-body').toggle();
  });

  $('.new-tasks-header').click(function () {
    $('.new-tasks-closed').toggle();
    $('.new-tasks-opened').toggle();
    $('.new-tasks-body').toggle();
  });

  $('.today-tasks-header').click(function () {
    $('.today-tasks-closed').toggle();
    $('.today-tasks-opened').toggle();
    $('.today-tasks-body').toggle();
  });

  $('.upcoming-tasks-header').click(function () {
    $('.upcoming-tasks-closed').toggle();
    $('.upcoming-tasks-opened').toggle();
    $('.upcoming-tasks-body').toggle();
  });

  //click complete task button
  body.on('click', '.complete-button', function () {
    let task_id = $(this).attr('data-id');
    toggleStatusAjax(task_id);
  });

  //toggle time mark dialog
  body.on('click', '.time-mark-button', function () {
    let task_id = $(this).attr('data-id');
    $('#mark-dialog-' + task_id).toggle(300);
  });

  //change user task assigned to
  body.on('click', '.asignee-button', function () {
    let task_id = $(this).attr('data-id');
    $('#asignee-dialog-' + task_id).toggle(300);
  });

  //RIGHT PANEL
  //close right panel

  //select project for task
  body.on('click', '.edit-add-task-to-project span', function () {
    $('.edit-task-to-project-dialog').show(300);
  });

  body.on('click', '.edit-add-task-to-project-dialog-project', function () {
    let project_id = $(this).attr('data-id');
    let project_name = $(this).text();
    let workspace_id = $(this).attr('data-workspace_id');
    $('#edit-task-project-input').val(project_id);
    $('#edit-task-workspace-input').val(workspace_id);
    $('.edit-task-to-project-dialog').hide(300);
    $('.edit-add-task-to-project span').text(project_name);
    $('.clear-project').show();
  });

  body.on('click', '.clear-project', function () {
    $('#edit-task-project-input').val(0);
    $('.edit-add-task-to-project span').text('Добавить к проекту');
    $(this).hide();
  });

  //TASK MENU
  body.on('click', '#task-menu-button', function () {
    $('.task-menu-dialog').toggle(300);
  });

  //private button
  body.on('click', '#private-button', function () {
    let id = $('#edit-task-id-input').val();
    togglePrivateAjax(id);
  });

  //likes
  body.on('click', '#task-like-button', function () {
    let task_id = $('#edit-task-id-input').val();
    toggleLikeAjax(task_id, 'task');
  });

  body.on('click', '.like-button', function () {
    let project_id = $(this).attr('data-id');
    let type = $(this).attr('data-type');
    toggleLikeAjax(project_id, type);
  });

  //TAGS ACTIONS
  body.on('click', '#tags-button', function () {
    $('.tags-dialog').toggle(300);
  });

  body.on('click', '#new-tag-button', function () {
    if (!$('#new-tag-input').val() || $('#new-tag-input').val().trim() == '') {
      alert('Введите метку!')
    } else {
      let tag = $('#new-tag-input').val();
      createTagAjax(tag);
    }
  });

  body.on('click', '.tags-item-select-title', function () {
    let tag_id = $(this).attr('data-id');
    let task_id = $('#edit-task-id-input').val();
    addTagToTask(task_id, tag_id);
  });

  body.on('click', '.detach-tag', function () {
    let tag_id = $(this).attr('data-id');
    let task_id = $('#edit-task-id-input').val();
    if (confirm('Убрать метку?')) {
      removeTagFromTask(task_id, tag_id);
    }
  });

  body.on('click', '.delete-tag', function () {
    let tag_id = $(this).attr('data-id');
    if (confirm('Удалить метку?')) {
      deleteTagAjax(tag_id);
    }
  });

  //toggle section button
  body.on('click', '#toggle-section-button', function () {
    let id = $('#edit-task-id-input').val();
    toggleSectionAjax(id);
  });

  //task conversation button
  body.on('click', '#task-conversation-button', function () {
    if (path[1] == 'conversations') {
      $('.right-panel-edit').removeClass('col-md-12').addClass('col-md-6');
      $('.left-panel-conversations').show(300).removeClass('col-md-12').addClass('col-md-6');
      $('#close-edit-task-panel').show();
      $('#task-conversation-button').hide();
      $('.conversation-close-button').show();
      $('.show-task-button').hide();
      $('.task-menu-dialog').hide();
      if (sidebar_opened) {
        $('.left-panel-conversations').css('width', '38%').css('left', '25%');
        $('.right-panel-edit').css('width', '37%');
      } else {
        $('.left-panel-conversations').css('width', '50%').css('left', '0');
        $('.right-panel-edit').css('width', '50%');
      }
      conversation_panel_opened = true;
    } else {
      let conversation_id = $(this).attr('data-conversation_id');
      if (conversation_id == '') {
        let conversation_title = '';
        let user_id = current_user_id;
        let message_text = 'Обсуждение задачи создано';
        let attachment_id = null;
        let mentions = null;
        let workspace_id = $('#edit-task-workspace-input').val();
        let task_id = $('#edit-task-id-input').val();
        let new_task_conversation = true;
        createConversationAjax(conversation_title, user_id, message_text, attachment_id, mentions, workspace_id, task_id, new_task_conversation)
      } else {
        window.location.replace('/conversations/' + current_workspace_id + '/' + conversation_id + '/');
      }
    }
  });

  body.on('click', '.conversation-close-button', function () {
    $('.left-panel-conversations').hide(300);
    $('.right-panel-edit').show(300).removeClass('col-md-6').addClass('col-md-12');
    $('#close-edit-task-panel').hide();
    $('#task-conversation-button').show();
    if (sidebar_opened) {
      $('.right-panel-edit').css('width', '75%');
    } else {
      $('.right-panel-edit').css('width', '100%');
    }
    conversation_panel_opened = false;
  });

  body.on('click', '#close-edit-task-panel', function () {
    $('.right-panel-edit').hide(300);
    $('.left-panel-conversations').show(300).removeClass('col-md-6').addClass('col-md-12');
    $('.conversation-close-button').hide();
    $('.show-task-button').show();
    $('.conversations-item').removeClass('panel-full-height-small').addClass('panel-full-height');
    if (sidebar_opened) {
      $('.left-panel-conversations').css('width', '75%').css('left', '25%');
      $('.left-panel').css('width', '75%').css('left', '25%');
    } else {
      $('.left-panel-conversations').css('width', '100%').css('left', '0');
      $('.left-panel').css('width', '100%').css('left', '0');
    }
    task_panel_opened = false;
    $('.left-panel').addClass('col-md-12').removeClass('col-md-6');
  });

  body.on('click', '.show-task-button', function (e) {
    if (path[3]) {
      e.preventDefault();
      $('.right-panel-edit').show(300).removeClass('col-md-12').addClass('col-md-6');
      $('.left-panel-conversations').removeClass('col-md-12').addClass('col-md-6');
      $('#close-edit-task-panel').show();
      $('#task-conversation-button').hide();
      $('.conversation-close-button').show();
      $('.show-task-button').hide();
      $('.conversations-item').addClass('panel-full-height-small').removeClass('panel-full-height');
      if (sidebar_opened) {
        $('.left-panel-conversations').css('width', '38%').css('left', '25%');
        $('.right-panel-edit').css('width', '37%');
      } else {
        $('.left-panel-conversations').css('width', '50%').css('left', '0');
        $('.right-panel-edit').css('width', '50%');
      }
      task_panel_opened = true;
    }
  });

  //click complete button in right panel
  body.on('click', '#edit-project-status', function () {
    let current_status = $('#edit-task-status-input').val();
    switch (current_status) {
      case 1:
        if (confirm('Завершить задачу?')) {
          $(this).removeClass('edit-project-status').addClass('edit-project-status-active');
          $('#edit-task-status-input').val(3);
        }
        break;
      case 3:
        $(this).addClass('edit-project-status').removeClass('edit-project-status-active');
        $('#edit-task-status-input').val(1);
        break;
      case 4:
        if (confirm('Завершить секцию?')) {
          $(this).removeClass('edit-project-status').addClass('edit-project-status-active');
          $('#edit-task-status-input').val(5);
        }
        break;
      case 5:
        $(this).addClass('edit-project-status').removeClass('edit-project-status-active');
        $('#edit-task-status-input').val(4);
        break;
    }
    toggleStatusAjax($('#edit-task-id-input').val());
  });

  //attaching files to task
  body.on('click', '#edit-task-attach-button', function () {
    $('#task-attach-popup').toggle(300);
  });

  //close attach window
  body.on('click', '.upload-close', function () {
    $('#task-attach-popup').hide(300);
  });

  //delete task
  body.on('click', '#edit-task-delete-button', function () {
    if (confirm('Удалить?')) {
      let task_id = $(this).attr('data-id');
      deleteTaskAjax(task_id);
    }
  });

  //copy task url
  body.on('click', '#copy-url-button', function () {
    let task_id = $('#edit-task-id-input').val();
    let url = document.location.protocol + '//' + document.location.host + '/list/' + current_workspace_id + '/' + current_user_id + '/0/0/' + task_id;
    $('#task-url').text(url);
    copyToClipboard($('#task-url'));
  });

  //task due date
  if (!$('#edit-task-due-date-input').val() || $('#edit-task-due-date-input').val().trim() == '') {
    $('.clear-due_date').hide();
  } else {
    $('.clear-due_date').show();
  }

  $.datepicker.setDefaults($.datepicker.regional["ru"]);

  $('#edit-task-due-date-input').datepicker({
    constrainInput: true,
    dateFormat    : 'dd.mm.yy'
  });

  body.on('focusout', '#edit-task-due-date-input', function () {
    if (!$(this).val() || $(this).val().trim() == '') {
      $('.clear-due_date').hide();
    } else {
      $('.clear-due_date').show();
    }
  });

  $('#new-task-due-date-input').datepicker({
    constrainInput: true,
    dateFormat    : 'dd.mm.yy'
  });

  body.on('click', '.clear-due_date', function () {
    $('#edit-task-due-date-input').val('');
    $(this).hide();
  });

  body.on('change', '#edit-task-due-date-input', function () {
    if (!$('#edit-task-due-date-input').val() || $('#edit-task-due-date-input').val().trim() == '') {
      $('.clear-due_date').hide();
    } else {
      $('.clear-due_date').show();
    }
  });

  //calendar
  let calendar;

  if (document.getElementById('calendar')) {
    $(function () {
      $('#calendar').fullCalendar({
        lang            : 'ru',
        header          : {
          left  : 'today',
          center: 'title',
          right : 'prev,next',
        },
        buttonText      : {
          today: 'Сегодня',
          month: 'Месяц',
          week : 'Неделя',
          day  : 'День',
          list : 'Список'
        },
        themeSystem     : 'jquery-ui',
        theme           : true,
        themeButtonIcons: {
          prev    : 'circle-triangle-w',
          next    : 'circle-triangle-e',
          prevYear: 'seek-prev',
          nextYear: 'seek-next'
        },
        defaultView     : 'month',
        eventSources    : [
          {
            url      : '/api/tasks/getCalendarEvents/',
            type     : 'POST',
            data     : {
              workspace_id: path[2],
              user_id     : path[3],
              project_id  : path[4],
              filter_type : path[5]
            },
            error    : function () {
              console.log('there was an error while fetching events!');
            },
            color    : '#3e95cc',
            textColor: '#fff',
            className: 'calendar-link'
          }
        ],
        eventClick      : function (event) {
          let task_id = event.id;
          getTaskAjax(task_id);
        },
        dayClick        : function (date) {
          $('#new-task-due-date-input').val(date.format());
          $('.page-header').toggle(300);
          $('.page-body').toggle(300);
          $('#new-task-full-width').toggle();
        }
      });
      calendar = $('#calendar').fullCalendar('getCalendar');

      body.on('click', '#page-header-calendar', function () {
        setTimeout(function () {
          calendar.render();
          calendar.refetchEvents();
        }, 400);
      });
    });
  }

  //task description functions
  let quill;

  let users_list = {};
  getUserListAjax();

  if (document.getElementById("quill-editor")) {
    quill = new Quill('#quill-editor', {
      modules    : {
        toolbar          : '#quill-toolbar',
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
        "emoji-shortname": true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите описание задачи...',
      theme      : 'snow',
    });
  }

  body.on('click', '#task-description-block', function (e) {
    if (e.target.tagName != 'A') {
      $('#task-description-block .ql-toolbar').show();
      $('#task-description-block .ql-container').show();
      $('#description-preview').hide();
    }
  });

  body.mousedown(function (e) {
    let container = $("#task-description-block");
    if (container.has(e.target).length == 0) {
      $('#task-description-block .ql-toolbar').hide();
      $('#task-description-block .ql-container').hide();
      $('#description-preview').html(findLinks($('#task-description-block .ql-editor').html())).show();
    }
    if (e.target.id === 'edit-task-submit') {
      let result_text = $('#quill-editor .ql-editor').html();
      $('#quill-editor-target-input').val(result_text);
      updateTaskAjax();
    }
  });

  //submit edited task
  body.on('click', '#edit-task-submit', function (e) {
    e.preventDefault();
  });

  //comments editor
  let comment_quill;

  if (document.getElementById("edit-task-comment-quill-editor")) {
    comment_quill = new Quill('#edit-task-comment-quill-editor', {
      modules    : {
        toolbar          : '#edit-task-comment-quill-toolbar',
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
        "emoji-shortname": true,
        //"emoji-textarea" : true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите комментарий...',
      theme      : 'snow',
    });
  }

  //submit comment
  body.on('click', '#edit-task-comment-submit', function (e) {
    e.preventDefault();
    let result_text = $('#edit-task-comment-quill-editor .ql-editor').html();
    $('#edit-task-comment-input').val(result_text);
    if (!$('#edit-task-comment-input').val() || $('#edit-task-comment-input').val().trim() == '') {
      alert('Введите текст комментария!')
    } else {
      sendCommentAjax();
    }
  });

  //delete comment
  body.on('click', '.comment-delete-button', function () {
    if (confirm('Удалить?')) {
      let comment_id = $(this).attr('data-id');
      deleteCommentAjax(comment_id);
    }
  });

  //hide more than 5 comments
  hideComments();

  body.on('click', '.hidden-comments-button', function () {
    $('.hidden-comments-block').show();
    $(this).hide();
  });

  //open editing comment modal
  body.on('click', '.comment-edit-button', function () {
    let comment_id = $(this).attr('data-id');
    let comment_text = $('#comment-text-' + comment_id).attr('data-content');
    $('#comment-item-' + comment_id).after('' +
      '<div id="edit-comment-quill-block">' +
      '  <div id="edit-comment-quill-editor" data-id="' + comment_id + '"></div>' +
      '    <div id="edit-comment-quill-toolbar">' +
      '      <button class="ql-bold"></button>\n' +
      '      <button class="ql-italic"></button>\n' +
      '      <button class="ql-underline"></button>\n' +
      '      <button class="ql-strike"></button>' +
      '    </div>' +
      '  <div class="divider2"></div>' +
      '</div>'
    ).hide();
    let edit_comment_quill;

    edit_comment_quill = new Quill('#edit-comment-quill-editor', {
      modules    : {
        toolbar          : '#edit-comment-quill-toolbar',
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
        "emoji-shortname": true,
        //"emoji-textarea" : true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите комментарий...',
      theme      : 'snow',
    });

    $('#edit-comment-quill-editor .ql-editor').html(comment_text);
    $('#edit-comment-quill-block').focus();
  });

  body.mouseup(function (e) {
    let container = $("#edit-comment-quill-block");
    if (document.getElementById("edit-comment-quill-block") && container.has(e.target).length == 0) {
      let comment_id = $('#edit-comment-quill-editor').attr('data-id');
      let comment_text = $('#edit-comment-quill-editor .ql-editor').html();
      $('#edit-comment-quill-block').remove();
      $('#comment-item-' + comment_id).show();
      updateCommentAjax(comment_id, comment_text);
    }
  });

  //TASKS ACTIONS
  $('.new-task-button').click(function (e) {
    e.preventDefault();
    $('#new-task-due-date-input').val('');
    $('.page-header').toggle(300);
    $('.page-body').toggle(300);
    $('#new-task-full-width').toggle();
    $('#new-conversation-dialog').hide();
    $('#new-project-dialog').hide();
  });

  $('#close-create-task-form').click(function (e) {
    e.preventDefault();
    $('.page-header').toggle(300);
    $('.page-body').toggle(300);
    $('#new-task-full-width').toggle();
  });

  body.on('click', '.project-to-new-task span', function () {
    $('.project-to-new-task-dialog').show(300);
  });

  body.on('click', '.project-to-new-task-dialog-item', function () {
    let project_id = $(this).attr('data-id');
    let project_name = $(this).text();
    $('#create-task-form-project_id-input').val(project_id);
    $('.project-to-new-task-dialog').hide(300);
    $('.project-to-new-task span').text(project_name);
  });

  body.on('click', '#new-task-status', function () {
    if ($('#create-task-form-status-input').val() == 1) {
      $(this).removeClass('edit-project-status').addClass('edit-project-status-active');
      $('#create-task-form-status-input').val(3);
    } else {
      $(this).addClass('edit-project-status').removeClass('edit-project-status-active');
      $('#create-task-form-status-input').val(1);
    }
  });

  let create_task_quill;

  if (document.getElementById("create-quill-editor")) {
    new Quill('#create-quill-editor', {
      modules    : {
        toolbar          : '#create-quill-toolbar',
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
        "emoji-shortname": true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите описание задачи...',
      theme      : 'snow',
    });
  }

  body.on('click', '#create-task-description-block', function () {
    $('#create-task-description-block .ql-toolbar').show();
    $('#create-task-description-block .ql-container').show();
    $('#create-description-preview').hide();
  });

  body.mousedown(function (e) {
    let container = $("#create-task-description-block");
    if (container.has(e.target).length == 0) {
      $('#create-task-description-block .ql-toolbar').hide();
      $('#create-task-description-block .ql-container').hide();
      $('#create-description-preview').html(findLinks($('#create-task-description-block .ql-editor').html())).show();
    }
    if (e.target.id === 'create-task-form-submit') {
      let result_text = $('#create-quill-editor .ql-editor').html();
      $('#create-quill-editor-target-input').val(result_text);
      let delegated_id = $('#create-task-form-delegated_id-input').val();
      let status = $('#create-task-form-status-input').val();
      let project_id = $('#create-task-form-project_id-input').val();
      let workspace_id = $('#create-task-form-workspace_id-input').val();
      let title = $('#create-task-form-title-input').val();
      let text = $('#create-quill-editor-target-input').val();
      let owner_id = $('#create-task-form-owner_id-input').val();
      let attachment_ids = $('#create-task-form-attachment_ids-input').val();
      let due_date = $('#new-task-due-date-input').val();
      createTaskAjax(owner_id, delegated_id, status, project_id, workspace_id, title, text, attachment_ids, due_date);
    }
  });

  //submit new task
  body.on('click', '#create-task-form-submit', function (e) {
    e.preventDefault();
  });

  $('.create-section-button').click(function () {
    $('#edit-task-title-input').val('');
    $('#quill-editor-target-input').val('');
    $('#description-preview').empty();
    let project_id = $('#create-task-project-input').val();
    let user_id = $('#user-id').val();
    let workspace_id = $('#workspace-id').val();
    createEmptyTaskAjax(project_id, user_id, workspace_id, 4);
  });

  $('.create-task-button').click(function () {
    $('#edit-task-title-input').val('');
    $('#quill-editor-target-input').val('');
    $('#description-preview').empty();
    let project_id = $('#create-task-project-input').val();
    let user_id = $('#user-id').val();
    let workspace_id = $('#workspace-id').val();
    createEmptyTaskAjax(project_id, user_id, workspace_id);
  });

  //SUBTASKS ACTIONS
  body.on('click', '#create-subtask-button', function () {
    let result_text = $('#quill-editor .ql-editor').html();
    $('#quill-editor-target-input').val(result_text);
    updateTaskAjax(true);
  });

  //subtasks filter
  body.on('click', '#subtasks-filter-button', function () {
    $('.subtasks-filter-dialog').toggle(300);
  });

  body.on('change', '.subtasks-filter-dialog input', function () {
    let status = $(this).val();
    switch (status) {
      case '1':
        $('.subtask-status-3').hide();
        $('.subtask-status-1').show();
        break;
      case '2':
        $('.subtask-status-3').show();
        $('.subtask-status-1').hide();
        break;
      case '3':
        $('.subtask-status-3').show();
        $('.subtask-status-1').show();
        break;
    }
    $('.subtasks-filter-dialog').hide();
  });

  //CONVERSATIONS ACTIONS
  $('.new-conversation-button').click(function (e) {
    $('#new-conversation-dialog').hide();
    $('#new-project-dialog').hide();
    $('#new-task-full-width').hide();
    $('.page-header').show();
    $('.page-body').show();
    e.preventDefault();
    $('#new-conversation-dialog').toggle(300);
  });

  //close conversation creation modal
  body.on('click', '.new-conversation-close', function () {
    $('#new-conversation-dialog').hide(300);
  });

  if (document.getElementById("new-conversation-quill-editor")) {
    new Quill('#new-conversation-quill-editor', {
      modules    : {
        toolbar          : '#new-conversation-quill-toolbar',
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
        "emoji-shortname": true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите сообщение...',
      theme      : 'snow',
    });
  }

  $('#new-conversation-attach-input').change(function () {
    let fd = new FormData();
    fd.append("attachment", this.files[0]);
    uploadConversationAttachAjax(fd);
  });

  $('#new-conversation-submit').click(function () {
    let user_id = $('#current-user').val();
    let attachment_id = $('#new-conversation-attachment-id-input').val();
    let conversation_title = $('#new-conversation-title').val();
    let message_text = $('#new-conversation-quill-editor  .ql-editor').html();
    let mentions = [];
    let workspace_id = $('#workspace-id').val();
    $('#new-conversation-quill-editor .ql-editor .mention').each(function (index, mention) {
      mentions.push($(mention).data('id'));
    });
    if (message_text || attachment_id) {
      createConversationAjax(conversation_title, user_id, message_text, attachment_id, mentions, workspace_id);
    } else {
      alert('Введите текст сообщения или добавьте изображение');
    }
  });

  //PROJECTS ACTIONS
  //open project creation modal
  $('.new-project-button').click(function (e) {
    e.preventDefault();
    $('#new-conversation-dialog').hide();
    $('#new-task-full-width').hide();
    $('.page-header').show();
    $('.page-body').show();
    $('#new-project-dialog').toggle(300);
  });

  //close project creation modal
  body.on('click', '.new-project-close', function () {
    $('#new-project-dialog').hide(300);
  });

  //submiting new project
  body.on('click', '#new-project-create', function () {
    let validate = true;
    let workspace_id = $('#new-project-workspace_id').val();
    let title = $('#new-project-title').val();
    let text = $('#new-project-text-quill-editor .ql-editor').html();
    let alias = $('#new-project-alias').val();
    if (!title || title.trim() == '') {
      alert('Введите название проекта!');
      validate = false;
    }
    if (workspace_id == 0) {
      alert('Выберите отдел!');
      validate = false;
    }
    if (validate) {
      createProjectAjax(workspace_id, title, text, alias);
    }
  });

  //open project edition modal
  body.on('click', '.project-edit-button', function () {
    $('.edit-project-dialog').toggle(300);
  });

  //close project edition modal
  body.on('click', '.edit-project-close', function () {
    $('.edit-project-dialog').toggle(300);
  });

  //edit project quill
  if (document.getElementById("edit-project-text-quill-editor")) {
    new Quill('#edit-project-text-quill-editor', {
      modules    : {
        toolbar          : '#edit-project-text-quill-toolbar',
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
        "emoji-shortname": true,
        //"emoji-textarea" : true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите описание...',
      theme      : 'snow',
    });
  }

  //new project quill
  if (document.getElementById('new-project-text-quill-editor')) {
    new Quill('#new-project-text-quill-editor', {
      modules    : {
        toolbar          : '#new-project-text-quill-toolbar',
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
        "emoji-shortname": true,
        "emoji-toolbar"  : true
      },
      placeholder: 'Введите описание...',
      theme      : 'snow',
    });
  }

  //submiting edited project
  body.on('click', '#edit-project-submit', function () {
    let validate = true;
    let id = $('#edit-project-id').val();
    let workspace_id = $('#edit-project-workspace_id').val();
    let title = $('#edit-project-title').val();
    let text = $('#edit-project-text-quill-editor .ql-editor').html();
    let alias = $('#edit-project-alias').val();
    if (workspace_id == 0) {
      alert('Выберите отдел!');
      validate == false;
    }
    if (!title || title.trim == '') {
      alert('Введите название!');
      validate == false;
    }
    if (validate) {
      updateProjectAjax(id, workspace_id, title, text, alias);
    }
  });

  //FOLLOWERS BLOCK
  body.on('click', '.follow-button-block', function () {
    let post_id = $(this).attr('data-post_id');
    let type = $(this).attr('data-type');
    let user_id = $('#current-user').val();
    toggleFollowerAjax(post_id, user_id, type);
  });

  body.on('click', '.add-follower-button', function () {
    $('.followers-dialog').toggle(300);
  });

  body.on('click', '.followers-dialog li', function () {
    let post_id = $(this).attr('data-post_id');
    let user_id = $(this).attr('data-user_id');
    let type = $(this).attr('data-type');
    toggleFollowerAjax(post_id, user_id, type);
  });

  //IMAGE MODAL WINDOW
  //open image modal window
  body.on('click', '.img-preview', function () {
    let img_url = $(this).attr('data-url');
    showImageModal(img_url);
  });

  body.on('click', '.img-small-preview', function () {
    let img_url = $(this).attr('data-url');
    showImageModal(img_url);
  });

  //close image modal window
  body.mouseup(function (e) {
    let container = $("#image-modal");
    if (container.has(e.target).length == 0) {
      container.hide();
    }
  });

  body.on('click', '.close-image-modal', function () {
    $('#image-modal').hide();
  });

  //FILES
  //delete file
  body.on('click', '.file-delete', function () {
    let file_id = $(this).attr('data-id');
    if (confirm('Удалить файл?')) {
      deleteFileAjax(file_id);
    }
  });

  //USERS POPUP
  body.on('mouseenter', '.users-item', function () {
    $('#user-popup').slideDown("fast")
      .css('backgroundColor', $(this).css('backgroundColor'))
      .css('backgroundImage', $(this).css('backgroundImage'));
    if (cursorX + 200 > $(document).width()) {
      $('#user-popup').css('left', cursorX - 200);
    } else {
      $('#user-popup').css('left', cursorX);
    }
    if (cursorY < 170) {
      $('#user-popup').css('top', cursorY);
    } else {
      $('#user-popup').css('top', cursorY - 170);
    }
    $('#user-popup-fullname').empty().text($(this).attr('data-user_fullname'));
    $('#user-popup-department').empty().text($(this).attr('data-user_department'));
    $('#user-popup-office').empty().text($(this).attr('data-user_office'));
  });

  body.on('mouseleave', '#user-popup', function () {
    $('#user-popup').slideUp("fast");
  });

  body.mouseup(function (e) {
    let container = $("#user-popup");
    if (container.has(e.target).length == 0) {
      container.slideUp('fast');
    }
  });

  //BELL POPUP
  //USERS POPUP
  body.on('mouseenter', '.notification-bell', function () {
    $('.notification-bell-tooltip').slideDown("fast");
  });

  body.on('mouseleave', '.notification-bell-tooltip', function () {
    $('.notification-bell-tooltip').slideUp("fast");
  });

  body.mouseup(function (e) {
    let container = $(".notification-bell-tooltip");
    if (container.has(e.target).length == 0) {
      container.slideUp('fast');
    }
  });

  //*END INTERFACE*//

  //*FUNCTIONS*//
  //shared functions

  function addTagToTask(task_id, tag_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/tasks/' + task_id + '/addTag/',
      dataType: "json",
      type    : 'PUT',
      data    : {
        tag_id: tag_id
      },
      success : function (data) {
        $('.loading').hide();
        $('.tags-dialog').hide(300);
        $('.tags-block').append('' +
          '<div class="tags-item task-tag"' +
          '     id="task-tag-' + data.success.id + '"' +
          '     data-id="' + data.success.id + '"' +
          '     style="background-color: ' + data.success.color + '"' +
          '>' +
          '  <a href="/list/' + current_workspace_id + '/0/0/6/' + data.success.id + '">' +
          '    <i class="fa fa-tag" aria-hidden="true"></i> ' + data.success.title +
          '  </a>' +
          '  <span class="detach-tag crm-button" data-id="' + data.success.id + '">' +
          '    <i class="fa fa-times" aria-hidden="true"></i>' +
          '  </span>' +
          '</div>' +
          '');
        $('#tag-' + data.success.id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function appendNewTaskInMyTasks(data) {
    let alias_string = '';
    if (data.success.project && data.success.project.alias) {
      alias_string = '<b>' + data.success.project.alias + ' </b>';
    }
    let title_string = 'Без названия';
    let class_string = '';
    if (data.success.title) {
      title_string = data.success.title;
    } else {
      class_string = 'empty-title';
    }
    let time_mark_string = '' +
      '<div class="float-right time-mark-button" data-id="' + data.success.id + '"></div>\n' +
      '<div class="dialog mark-dialog" id="mark-dialog-' + data.success.id + '">\n' +
      '  <h3>Пометить задачу</h3>\n' +
      '  <ul>\n' +
      '    <li class="mark-new time-mark" data-id="' + data.success.id + '" data-mark="new">Новая</li>\n' +
      '    <li class="mark-today time-mark" data-id="' + data.success.id + '" data-mark="today">Сегодня</li>\n' +
      '    <li class="mark-upcoming time-mark" data-id="' + data.success.id + '" data-mark="upcoming">Предстоящая</li>\n' +
      '  </ul>\n' +
      '</div>';
    if (data.success.delegated_id == $('#user-id').val()) {
      $('.new-tasks-body').prepend('' +
        '<div class="task-item task-visibly-status-' + data.success.status + ' ' + data.success.mark_class + '" id="task-item-' + data.success.id + '">' +
        '  <input type="hidden" name="sort[]" value="' + data.success.id + '">' +
        '  <span class="drag-button">' +
        '    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
        '    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
        '  </span>' +
        '  <span class="complete-button complete-status-' + data.success.status + '" data-id="' + data.success.id + '" id="complete-button-' + data.success.id + '">\n' +
        '    <i class="fa fa-check-circle-o" aria-hidden="true"></i>\n' +
        '  </span>\n' +
        '  <span class="task-title">\n' +
        '    <a href="/list/' + data.success.workspace_id + '/' + data.success.delegated_id + '/0/0/' + data.success.id + '" ' +
        '       class="task-link" ' +
        '       data-id="' + data.success.id + '" ' +
        '       id="task-link-' + data.success.id + '"' +
        '    >\n' +
        '      <span class="' + class_string + '">' +
        alias_string + title_string +
        '      </span>' +
        '    </a>\n' +
        '  </span>\n' +
        '  <span class="float-right green" id="task-created-label-' + data.success.id + '">Задача успешно создана!</span>' +
        time_mark_string +
        '</div>');
    }
  }

  function appendNewTaskInProject(data) {
    let alias_string = '';
    if (data.success.project && data.success.project.alias) {
      alias_string = '<b>' + data.success.project.alias + ' </b>';
    }
    let title_string = 'Без названия';
    let class_string = '';
    if (data.success.title) {
      title_string = data.success.title;
    } else {
      class_string = 'empty-title';
    }
    let users_string = '';
    for (var i = 0; i < data.success.users.length; i++) {
      if (data.success.users[i].id != data.success.user.id) {
        users_string = users_string + '' +
          '<option value="' + data.success.users[i].id + '" ' +
          '        class="asignee-option"' +
          '>' +
          data.success.users[i].name +
          '</option>';
      }
    }
    user_assigned_string = '' +
      '<div class="float-right asignee-button users-item users-item-small"' +
      '     data-id="' + data.success.id + '"' +
      '     data-user_id="' + data.success.user.id + '"' +
      '     data-user_fullname="' + data.success.user.fullname + '"' +
      '     data-user_department="' + data.success.user.department + '"' +
      '     data-user_office="' + data.success.user.office + '"' +
      '     style="background-color: ' + data.success.user.color + '; ' +
      '            background-image: ' + data.success.user.background_color + ';"' +
      '     id="asignee-button-' + data.success.id + '"' +
      '>' +
      data.success.first_letters +
      '</div>' +
      '<div class="dialog asignee-dialog" ' +
      '     data-id="' + data.success.id + '" ' +
      '     id="asignee-dialog-' + data.success.id + '"' +
      '>' +
      '  <h3>Назначить задачу</h3>' +
      '  <select id="asignee-select-' + data.success.id + '" ' +
      '          class="asignee-select" ' +
      '          data-id="' + data.success.id + '"' +
      '  >' +
      '    <option value="' + data.success.user.id + '" ' +
      '            selected ' +
      '            class="form-control"' +
      '    >' +
      data.success.user.name +
      '    </option>' +
      users_string +
      '  </select>' +
      '  <p id="asignee-user-link-' + data.success.id + '">' +
      '    <a href="/list/' + data.success.user.workspace_id + '/' + data.success.user.id + '/0/0">' +
      '      Посмотреть задачи пользователя ' + data.success.user.name +
      '    </a>' +
      '  </p>' +
      '</div>';
    $('.project-all-tasks-body').prepend('' +
      '<div class="task-item task-visibly-status-' + data.success.status + ' ' + data.success.mark_class + '" id="task-item-' + data.success.id + '">' +
      '  <input type="hidden" name="sort[]" value="' + data.success.id + '">' +
      '  <span class="drag-button">' +
      '    <i class="fa fa-ellipsis-v" aria-hidden="true"></i><i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
      '  </span>\n' +
      '  <span class="complete-button complete-status-' + data.success.status + '" data-id="' + data.success.id + '" id="complete-button-' + data.success.id + '">\n' +
      '    <i class="fa fa-check-circle-o" aria-hidden="true"></i>\n' +
      '  </span>\n' +
      '  <span class="task-title">\n' +
      '    <a href="/list/' + data.success.workspace_id + '/' + data.success.delegated_id + '/0/0/' + data.success.id + '" ' +
      '       class="task-link" ' +
      '       data-id="' + data.success.id + '" ' +
      '       id="task-link-' + data.success.id + '"' +
      '    >' +
      '      <span class="' + class_string + '">' +
      alias_string + title_string +
      '      </span>' +
      '    </a>\n' +
      '  </span>\n' +
      '  <span class="float-right green" id="task-created-label-' + data.success.id + '">Задача успешно создана!</span>' +
      user_assigned_string +
      '</div>');
  }

  function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
  }

  function createConversationAjax(conversation_title, user_id, message_text, attachment_id, mentions, workspace_id, task_id, new_task_conversation) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/conversations/",
      dataType: "json",
      type    : 'POST',
      data    : {
        title        : conversation_title,
        owner_id     : user_id,
        message_text : message_text,
        attachment_id: attachment_id,
        mentions     : mentions,
        workspace_id : workspace_id,
        task_id      : task_id
      },
      success : function (data) {
        $('.loading').hide();
        $('#new-conversation-dialog').hide();
        $('#new-conversation-title').val('');
        $('#new-conversation-quill-editor .ql-editor').empty();
        $('#new-conversation-attachment-block').empty();
        let attachment_string = '';
        if (data.success.message.attachment_id) {
          attachment_string = '' +
            '<div class="message-attachment">' +
            '  <img class="img-small-preview" src="/download/' + data.success.message.attachment_id + '"' +
            '       style="max-width: 50%; margin-top: 10px; margin-bottom: 10px;"' +
            '       data-url="/download/' + data.success.message.attachment_id + '"' +
            '  >' +
            '</div>'
        }
        let followers_string = '';
        for (var i = 0; i < data.success.users.length; i++) {
          if (data.success.users[i].id != data.success.conversation.owner_id) {
            followers_string = followers_string +
              '<li data-user_id="' + data.success.users[i].id + '" data-post_id="' + data.success.conversation.id + '" data-type="conversation"' +
              '    class="follower-list-item-' + data.success.users[i].id + '">' +
              data.success.users[i].name +
              '  <i class="fa fa-plus-square-o toggle-follower" aria-hidden="true"></i>' +
              '</li>';
          }
        }
        $('#dialogs').prepend('' +
          '<li id="dialog-item-' + data.success.conversation.id + '" ' +
          '    class="dialog-item"' +
          '    data-id="' + data.success.conversation.id + '" ' +
          '    data-workspace_id="' + data.success.conversation.workspace_id + '" ' +
          '>' +
          '  <div class="dialog-photo">' +
          '    <a href="#"' +
          '       class="users-item users-item-big"' +
          '       style="background-color: ' + data.success.conversation.user.color + ';' +
          '       background-image: ' + data.success.conversation.user.background_image + ';"' +
          '       data-user_fullname="' + data.success.conversation.user.fullname + '"' +
          '       data-user_department="' + data.success.conversation.user.department + '"' +
          '       data-user_office="' + data.success.conversation.user.office + '"' +
          '    >' +
          data.success.conversation.user.first_letters +
          '    </a>' +
          '  </div>' +
          '  <div class="dialog-content">' +
          '    <div class="dialog-date">' + data.success.conversation.date + '</div>' +
          '    <div class="dialog-delete crm-button conversation-delete-button"' +
          '         data-id="' + data.success.conversation.id + '"' +
          '    >' +
          '      <i class="fa fa-trash-o" aria-hidden="true"></i>' +
          '    </div>' +
          '    <div class="dialog-name">' +
          '      <a href="/conversations/' + data.success.conversation.workspace_id + '/' + data.success.conversation.id + '/">' +
          data.success.conversation.title +
          '      </a>' +
          '    </div>' +
          '    <div class="dialog-text-preview">' +
          data.success.conversation.text_preview +
          '    </div>' +
          '  </div>' +
          '</li>' +
          '');
        if (document.getElementById('new-message-quill-editor-' + data.success.conversation.id)) {
          new Quill('#new-message-quill-editor-' + data.success.conversation.id, {
            modules    : {
              toolbar          : '#new-message-quill-toolbar-' + data.success.conversation.id,
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
              "emoji-shortname": true,
              //"emoji-textarea" : true,
              "emoji-toolbar"  : true
            },
            placeholder: 'Введите сообщение...',
            theme      : 'snow',
          });
        }
        if (new_task_conversation) {
          window.location.replace('/conversations/' + data.success.conversation.workspace_id + '/' + data.success.conversation.id + '/');
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function createEmptyTaskAjax(project_id, implementer_id, workspace_id, status, parent_id) {
    $('.loading').show();
    ajaxSetup();
    if (!project_id) {
      project_id = 0;
    }
    $.ajax({
      url     : "/api/tasks/createEmpty/",
      dataType: "json",
      type    : 'POST',
      data    : {
        project_id    : project_id,
        implementer_id: implementer_id,
        workspace_id  : workspace_id,
        status        : status,
        parent_id     : parent_id
      },
      success : function (data) {
        $('.loading').hide();
        task_in_progress = 1;
        getTaskAjax(data.success.id);
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function createProjectAjax(workspace_id, title, text, alias) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/projects/",
      dataType: "json",
      type    : "POST",
      data    : {
        workspace_id: workspace_id,
        title       : title,
        text        : text,
        owner_id    : $('#owner_id').val(),
        status      : 1,
        alias       : alias
      },
      success : function (data) {
        let description = '';
        if (data.success.text) {
          description = data.success.text;
        }
        $('.loading').hide();
        $('#new-project-dialog').hide();
        $('.new-project-workspace_id-option').attr('selected', false);
        $('#new-project-title').val('');
        $('#new-project-text-quill-editor .ql-editor').empty();
        $('#new-project-alias').val('');
        $('.edit-task-to-project-dialog ul').prepend('' +
          '<li class="edit-add-task-to-project-dialog-project"' +
          '    data-id="' + data.success.id + '"' +
          '    data-workspace_id="' + data.success.workspace_id + '"' +
          '>' +
          data.success.title +
          '</li>');
        $('.project-to-new-task-dialog ul').prepend('' +
          '<li class="edit-add-task-to-project-dialog-project"' +
          '    data-id="' + data.success.id + '"' +
          '    data-workspace_id="' + data.success.workspace_id + '"' +
          '>' +
          data.success.title +
          '</li>');
        $('.projects-body').append('' +
          '<li>' +
          '  <a href="/list/' + data.success.workspace_id + '/0/' + data.success.id + '/1" ' +
          '     id="left-menu-project-' + data.success.id + '"' +
          '  >' +
          data.success.title +
          '  </a>' +
          '</li>');
        $('.projects-panel').prepend(
          '<div class="panel panel-default" id="project-item-' + data.success.id + '">' +
          '  <div class="panel-heading">' +
          '    <h3>' +
          '      <a href="/list/' + data.success.workspace_id + '/0/' + data.success.id + '/1" ' +
          '         id="project-title-' + data.success.id + '"' +
          '      >' +
          data.success.title +
          '      </a>' +
          '      <div class="float-right edit-project crm-button" data-id="' + data.success.id + '">' +
          '        <i class="fa fa-pencil" aria-hidden="true"></i>' +
          '      </div>' +
          likeTemplate(data.success, 'project') +
          '      </h3>' +
          '    </div>' +
          '    <div class="panel-body">' +
          '      <p id="project-text-' + data.success.id + '">' + description + '</p>' +
          '      <h4>Задач в проекте</h4>' +
          '      <table class="table text-center">' +
          '        <tr>' +
          '          <th>Всего</th>' +
          '          <th>Выполненно</th>' +
          '          <th>В работе</th>' +
          '        </tr>' +
          '        <tr>' +
          '          <td>0</td>' +
          '          <td>0</td>' +
          '          <td>0</td>' +
          '        </tr>' +
          '      </table>' +
          '      <div class="progress">' +
          '        <div class="progress-bar" ' +
          '             role="progressbar" ' +
          '             style="width: 0%"' +
          '             aria-valuenow="0" ' +
          '             aria-valuemin="0" ' +
          '             aria-valuemax="100"' +
          '        >' +
          '          0%' +
          '        </div>' +
          '      </div>' +
          '    </div>' +
          '  </div>');
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function createTagAjax(tag) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tags/",
      dataType: "json",
      type    : 'POST',
      data    : {
        tag: tag
      },
      success : function (data) {
        $('.loading').hide();
        $('#new-tag-input').val('');
        if (data.success.is_new == 'old') {
          alert('Такая метка уже есть в вашем списке');
        } else {
          $('#tags-list').append('' +
            '<div class="tags-item tags-item-select"' +
            '     id="tag-' + data.success.id + '"' +
            '     data-id="' + data.success.id + '"' +
            '     style="background-color: ' + data.success.color + '"' +
            '     title="Добавить к задаче"' +
            '>' +
            '  <span class="tags-item-select-title"' +
            '        data-id="' + data.success.id + '"' +
            '  >' +
            '    <i class="fa fa-tag" aria-hidden="true"></i> ' + data.success.title +
            '  </span>' +
            '  <span class="delete-tag crm-button"' +
            '        data-id="' + data.success.id + '"' +
            '        title="Удалить метку"' +
            '  >' +
            '    <i class="fa fa-times" aria-hidden="true"></i>\n' +
            '  </span>' +
            '</div>' +
            '');
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function createTaskAjax(owner_id, delegated_id, status, project_id, workspace_id, title, text, attachment_ids, due_date) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tasks/",
      dataType: "json",
      type    : "POST",
      data    : {
        owner_id      : owner_id,
        implementer_id: delegated_id,
        delegated_id  : delegated_id,
        status        : status,
        project_id    : project_id,
        workspace_id  : workspace_id,
        title         : title,
        text          : text,
        is_visible    : 1,
        time_mark     : 'new',
        attachment_ids: attachment_ids,
        due_date      : due_date
      },
      success : function (data) {
        $('.loading').hide();
        $('#create-tasks-attachments-block tbody').empty();
        $('#create-task-form-title-input').val('');
        $('#create-task-form-text-input').val('');
        $('#new-task-due-date-input').val('');
        $('#create-description-preview').empty();
        $('#create-quill-editor .ql-editor').empty();
        $('#new-task-full-width').hide(300);
        $('.page-header').show(300);
        $('.page-body').show(300);
        switch ($('#filter-type').val()) {
          case '0':
            appendNewTaskInMyTasks(data);
            break;
          case '1':
            appendNewTaskInProject(data);
            break;
        }
        order_data = $('.tasks-body input').serialize();
        saveOrderAjax(order_data);

        setTimeout(function () {
          $('#task-created-label-' + data.success.id).hide(800)
        }, 2500);
        if (data.success.project && $('#filter-type').val() == 0) {
          $('#task-item-' + data.success.id).append('' +
            '<div class="float-right project-title">\n' +
            '  <a href="/list/' + data.success.project.workspace_id + '/0/' + data.success.project.id + '/1" ' +
            '     style="background-color: ' + data.success.project.color + '"' +
            '  >\n' +
            data.success.project.title +
            '  </a>\n' +
            '</div>' +
            '');
          $('.project-title-' + data.success.id).html('' +
            '<span class="project-title">\n' +
            '  <a href="/list/' + data.success.project.workspace_id + '/0/' + data.success.project.id + '/1" ' +
            '     style="background-color: ' + data.success.project.color + '"' +
            '  >\n' +
            data.success.project.title +
            '  </a>\n' +
            '</span>' +
            '');
        }
        if (path[1] != 'list') {
          window.location.replace('/list/' + current_workspace_id + '/' + current_user_id + '/0/0/');
        }
        let task_name_string = '<span class="empty-title">Без названия</span>'
        if (data.success.title != null) {
          task_name_string = data.success.title;
        }
        for (var i = 0; i < data.success.files.length; i++) {
          let full_preview_string = '';
          if (data.success.files[i].type == 'jpeg' || data.success.files[i].type == 'jpg' || data.success.files[i].type == 'png'
            || data.success.files[i].type == 'gif') {
            preview_string = '<br/><img class="img-small-preview" src="/download/' + data.success.files[i].id + '" style="width: 100%; margin-top: 10px;">';
            full_preview_string = '  <div class="img-preview"\n' +
              '   style="background-image: url(\'/download/' + data.success.files[i].id + '\')">\n' +
              '  </div>\n';
          }
          $('.files-list').prepend('' +
            '<div class="file-item" id="file-item-' + data.success.files[i].id + '">\n' +
            ' <div class="file-list-author">\n' +
            '  <a href="/list/' + data.success.files[i].workspace_id + '/' + data.success.files[i].author + '/0/0" ' +
            '     class="users-item" ' +
            '     title="' + data.success.files[i].username + '"' +
            '     style="background-color: ' + data.success.files[i].color + '; ' +
            '            background-image: ' + data.success.files[i].background_image + '"' +
            '     data-user_fullname="' + data.success.files[i].fullname + '"' +
            '     data-user_department="' + data.success.files[i].department + '"' +
            '     data-user_office="' + data.success.files[i].office + '">' +
            data.success.files[i].first_letters +
            '  </a>' +
            ' </div>\n' +
            ' <div class="file-list-name">\n' +
            full_preview_string +
            '  <a href="/download/' + data.success.files[i].id + '">\n' +
            '   <img class="fileicon" src="/img/fileicons/' + data.success.files[i].type + '.png" alt="' + data.success.files[i].type + '">\n' +
            data.success.files[i].original_filename +
            '  </a>\n' +
            '  <span class="file-delete" data-id="' + data.success.files[i].id + '">\n' +
            '    <i class="fa fa-trash" aria-hidden="true"></i>\n' +
            '  </span>' +
            ' </div>\n' +
            ' <div class="file-list-task">\n' +
            '  Задача:\n' +
            '  <a href="/list/' + data.success.files[i].author + '/0/0/' + data.success.files[i].task_id + '" class="task-link" ' +
            'data-id="' + data.success.files[i].task_id + '">\n' +
            task_name_string +
            '  </a>\n' +
            ' </div>\n' +
            ' <hr>\n' +
            '</div>');
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function deleteCommentAjax(comment_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/comments/" + comment_id,
      dataType: "json",
      type    : 'DELETE',
      success : function (data) {
        $('.loading').hide();
        $('#comment-item-' + data.success.id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function deleteFileAjax(file_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/files/" + file_id,
      dataType: "json",
      type    : 'DELETE',
      success : function (data) {
        $('.loading').hide();
        $('#file-item-' + data.success.id).detach();
        $('#small-file-item-' + data.success.id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function deleteTagAjax(tag_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tags/" + tag_id,
      dataType: "json",
      type    : 'DELETE',
      success : function (data) {
        $('.loading').hide();
        $('#tag-' + data.success.id).detach();
        $('#task-tag-' + data.success.id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function deleteTaskAjax(task_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tasks/" + task_id,
      dataType: "json",
      type    : 'DELETE',
      success : function () {
        $('.loading').hide();
        $('.left-panel').addClass('col-md-12').removeClass('col-md-6');
        $('.right-panel-edit').hide();
        $('#task-item-' + task_id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function getUserListAjax() {
    ajaxSetup();
    $.ajax({
      url     : "/api/users/",
      dataType: "json",
      type    : 'GET',
      success : function (data) {
        users_list = data.success;
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function notification(data) {
    //sending message to chat for all users
    if (data.message_flag == 'new_message') {
      let message_class = '';
      let delete_string = '';
      if (data.author_id == $('#current-user').val()) {
        message_class = 'message-item-my';
        delete_string = '' +
          '<small class="float-right crm-button delete-message-button"' +
          '       id="delete-message-button-' + data.id + '"' +
          '       data-id="' + data.id + '"' +
          '>' +
          '  <i class="fa fa-trash" aria-hidden="true"></i>' +
          '</small>';
      } else {
        message_class = 'message-item-other';
      }
      let attachment_string = '';
      if (data.attachment_id) {
        attachment_string = '  <div class="message-attachment">\n' +
          '    <img class="img-small-preview" src="/download/' + data.attachment_id + '"\n' +
          '      style="max-width: 50%; margin-top: 10px; margin-bottom: 10px;"\n' +
          '      data-url="/download/' + data.attachment_id + '">\n' +
          '   </div>\n'
      }
      let text_string = '';
      if (data.text) {
        text_string = data.text;
      }
      $('#message-block-' + data.conversation_id).prepend('' +
        '<div class="message-item ' + message_class + '" id="message-item-' + data.id + '">' +
        '  <div class="message-photo">' +
        '    <a href="#"' +
        '       class="users-item users-item-big"' +
        '       style="background-color: ' + data.user.color + ';' +
        '       background-image: ' + data.user.background_image + ';"' +
        '       data-user_fullname="' + data.user.fullname + '"' +
        '       data-user_department="' + data.user.department + '"' +
        '       data-user_office="' + data.user.office + '"' +
        '     >' +
        data.user.first_letters +
        '     </a>' +
        '  </div>' +
        '  <div class="message-heading">' +
        '    <b class="message-author">' + data.author + '</b>' +
        delete_string +
        '    <small class="float-right crm-button reply-button reply-button-' + data.id + '"' +
        '           data-id="' + data.id + '"' +
        '    >' +
        '      <i class="fa fa-reply" aria-hidden="true"></i>' +
        '    </small>' +
        likeTemplate(data, 'message') +
        '    <small class="float-right">' + data.time + '</small>' +
        '  </div>' +
        '<div class="message-body">' +
        text_string +
        '</div>' +
        attachment_string +
        '</div>' +
        '');
      $('.left-panel-conversations').scrollTop(0);

      //update dialogs page
      $('#dialogs').prepend($('#dialog-item-' + data.conversation.id).detach().addClass('unread'));
      $('#dialog-item-' + data.conversation.id + ' .dialog-date').text(data.conversation.date);
      $('#dialog-item-' + data.conversation.id + ' .dialog-text-preview').html(data.conversation.text_preview);
      $('#dialog-item-' + data.conversation.id + ' .users-item-big')
        .css('background-color', data.conversation.user.color)
        .css('background-image', data.conversation.user.background_image)
        .attr('data-user_fullname', data.conversation.user.fullname)
        .attr('data-user_department', data.conversation.user.department)
        .attr('data-user_office', data.conversation.user.office)
        .text(data.conversation.user.first_letters);
    }

    //for current user
    if (data.recipient_id == $('#current-user').val()) {
      //play notification sound
      if ($('#enable-sound').val() == 1) {
        $('#notification-sound')[0].play();
      }

      //change page title
      if (active_notification < 5) {
        active_notification++;
        let notification_bullets = '';
        for (var i = 0; i < active_notification; i++) {
          notification_bullets = notification_bullets + '• ';
        }
        $(document).attr('title', notification_bullets + document_title);
      }

      //show notification bell and set tooltip text
      notification_title = data.text;
      localStorage.setItem('notification_title', notification_title);
      $('.notification-bell').show();
      $('.notification-bell-tooltip').html(notification_title);

      //sending to inbox
      switch (data.message_flag) {
        case 'new_message_comment':
          //message in followed conversation
          $('.comments-active-body').prepend('' +
            '<div class="comment-item row" data-id="' + data.id + '" id="inbox-item-' + data.id + '">\n' +
            '  <div class="col-md-11">' +
            '    <div>' +
            '      <span id="comment-conversation-item-' + data.conversation_id + '">' +
            '        Обсуждение' +
            '      </span>' +
            '    </div>' +
            '    <h3>' +
            '      <a href="/conversations/' + data.workspace_id + '/' + data.conversation_id + '">' +
            data.conversation_title +
            '      </a>' +
            '    </h3>' +
            '    <p>\n' +
            '      <b>Kodo CRM :</b>\n' +
            data.text +
            '      <small>' + data.created_at + '</small>\n' +
            '    </p>' +
            '    <hr>\n' +
            '  </div>\n' +
            '  <div class="col-md-1 move-to-archive" data-id="' + data.id + '">\n' +
            '    <i class="fa fa-times" id="move-to-archive-' + data.id + '" aria-hidden="true" title="Отправить в архив"></i>\n' +
            '    <i class="fa fa-undo" id="move-to-active-' + data.id + '" aria-hidden="true" title="Вернуть"\n' +
            '        style="display: none"></i>\n' +
            '  </div>\n' +
            '</div>' +
            '');
          break;
        case 'new_task_comment':
          //followed task message
          let project_string = '';
          let author_string = '';
          if (data.task.project_id) {
            project_string = '' +
              'в <span class="project-title project-title-' + data.task_id + '">' +
              '  <a href="/list/' + data.task.workspace_id + '/0/' + data.task.project_id + '/1" ' +
              '     style="background-color: ' + data.task.project.color + '"' +
              '  >' +
              data.task.project.title +
              '  </a>' +
              '</span>';
          }
          if (data.type = 'comment') {
            author_string = '<a href="/list/' + current_workspace_id + '/' + data.author_id + '/0/0">' +
              data.author +
              ': </a>';
          } else {
            author_string = data.author + ': ';
          }
          let alias_string = '';
          if (data.task.project) {
            alias_string = data.task.project.alias;
          }
          let title_string = 'Без названия';
          if (data.task_name) {
            title_string = data.task_name;
          }
          $('.comments-active-body').prepend('' +
            '<div class="comment-item row" data-id="' + data.id + '" id="inbox-item-' + data.id + '">\n' +
            '  <div class="col-md-11">\n' +
            '    <div>\n' +
            '      <span class="complete-button complete-status-' + data.task_status + '">\n' +
            '        <i class="fa fa-check-circle-o" aria-hidden="true"></i>\n' +
            '      </span>\n' +
            '      <span id="comment-task-item-' + data.task_id + '">Задача\n' +
            project_string +
            '      </span>\n' +
            '    </div>\n' +
            '    <h3>\n' +
            '      <a href="/list/' + current_workspace_id + '/' + current_user_id + '/0/0/' + data.task_id + '" ' +
            '         class="task-link"\n' +
            '         data-id="' + data.task_id + '" ' +
            '         id="task-link-' + data.task_id + '"' +
            '      >\n' +
            '        <b>' + alias_string + ' </b>\n' +
            title_string +
            '      </a>\n' +
            '    </h3>\n' +
            '    <p>\n' +
            '      <b>\n' +
            author_string +
            '      </b>\n' +
            data.text +
            '      <small>' + data.created_at + '</small>\n' +
            '    </p>\n' +
            '    <hr>\n' +
            '  </div>\n' +
            '  <div class="col-md-1 move-to-archive" data-id="' + data.id + '">\n' +
            '    <i class="fa fa-times" id="move-to-archive-' + data.id + '" aria-hidden="true" title="Отправить в архив"></i>\n' +
            '    <i class="fa fa-undo" id="move-to-active-' + data.id + '" aria-hidden="true" title="Вернуть"\n' +
            '                                               style="display: none"></i>\n' +
            '   </div>\n' +
            '</div>');
          break;
      }
    }
  }

  function removeTagFromTask(task_id, tag_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/tasks/' + task_id + '/removeTag/',
      dataType: "json",
      type    : 'PUT',
      data    : {
        tag_id: tag_id
      },
      success : function (data) {
        $('.loading').hide();
        $('#task-tag-' + data.success.id).detach();
        $('#tags-list').append('' +
          '<div class="tags-item tags-item-select"' +
          '     id="tag-' + data.success.id + '"' +
          '     data-id="' + data.success.id + '"' +
          '     style="background-color: ' + data.success.color + '"' +
          '     title="Добавить к задаче"' +
          '>' +
          '  <span class="tags-item-select-title"' +
          '        data-id="' + data.success.id + '"' +
          '  >' +
          '    <i class="fa fa-tag" aria-hidden="true"></i> ' + data.success.title +
          '  </span>' +
          '  <span class="delete-tag crm-button"' +
          '        data-id="' + data.success.id + '"' +
          '        title="Удалить метку"' +
          '  >' +
          '    <i class="fa fa-times" aria-hidden="true"></i>\n' +
          '  </span>' +
          '</div>' +
          '');
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function saveOrderAjax(data) {
    ajaxSetup();
    $.ajax({
      url   : '/api/tasks/saveOrder/',
      method: 'POST',
      data  : data
    });
  }

  function sendCommentAjax() {
    let mentions = [];
    $('#edit-task-comment-quill-editor .ql-editor .mention').each(function (index, mention) {
      mentions.push($(mention).data('id'));
    });
    $('#edit-task-comment-mentions').val(mentions);
    let msg = $('#create-comment-form').serialize();
    $('.loading').show();
    $('#edit-task-comment-quill-editor .ql-editor').empty();
    ajaxSetup();
    $.ajax({
      url     : "/api/comments/",
      dataType: "json",
      type    : "POST",
      data    : msg,
      success : function (data) {
        $('.loading').hide();
        commentTemplate(data.success);
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function showImageModal(img_url) {
    $('#image-modal-image').attr('src', img_url);
    $('#image-modal').show();
  }

  function sortTasksItems() {
    $('.all-tasks-body .task-item').each(function () {
      if ($(this).hasClass('time-mark-new')) {
        $('.new-tasks-body').append($(this).detach());
      }
      else if ($(this).hasClass('time-mark-today')) {
        $('.today-tasks-body').append($(this).detach());
      }
      else if ($(this).hasClass('time-mark-later')) {
        $('.later-tasks-body').append($(this).detach());
      }
    });
    if ($('#filter-type').val() != 5) {
      $('.task-visibly-status-3').hide();
      $('.task-visibly-status-5').hide();
    }
  }

  function titleToEmptyLinks() {
    $('.task-link').each(function () {
      if (($(this).text().trim() == '' || $(this).text().trim() == null) && !$(this).hasClass('crm-button')) {
        $(this).html('<span class="empty-title">Без названия</span>');
      }
    });
  }

  function toggleFollowerAjax(post_id, user_id, type) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/followers/toggle/",
      dataType: "json",
      type    : 'POST',
      data    : {
        post_id: post_id,
        user_id: user_id,
        type   : type
      },
      success : function (data) {
        $('.loading').hide();
        if (type == 'task') {
          if (data.success.followed == 'followed') {
            if ($('#current-user').val() == user_id) {
              $('.task-follow-button-block').addClass('follow-button-block-active');
              $('.task-follow-button-text').text(' Вы подписаны');
            }
            $('.task-follower-list-item-' + data.success.user.id + ' .toggle-follower').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
            $('.task-followers-list').append('' +
              '<a href="/list/' + data.success.user.workspace_id + '/' + data.success.user.id + '/0/0" ' +
              '  class="users-item task-followers-item-' + data.success.user.id + '"' +
              '  data-user_fullname="' + data.success.user.fullname + '"' +
              '  data-user_department="' + data.success.user.department + '"' +
              '  data-user_office="' + data.success.user.office + '"' +
              '  style="background-color: ' + data.success.user.color + '; ' +
              '         margin-left: 0; ' +
              '         margin-right: 0; ' +
              '         background-image: ' + data.success.user.background_image + '"' +
              '>' +
              data.success.user.first_letters +
              '</a>');
          } else {
            if ($('#current-user').val() == user_id) {
              $('.task-follow-button-block').removeClass('follow-button-block-active');
              $('.task-follow-button-text').text(' Подписаться');
            }
            $('.task-follower-list-item-' + data.success.user.id + ' .toggle-follower').addClass('fa-plus-square-o').removeClass('fa-minus-square-o');
            $('.task-followers-item-' + data.success.user.id).detach();
          }
        } else if (type == 'conversation') {
          if (data.success.followed == 'followed') {
            if ($('#current-user').val() == user_id) {
              $('#conversations-item-' + post_id + ' .conversation-follow-button-block')
                .addClass('follow-button-block-active')
                .attr('title', 'Отписаться');
            }
            $('#conversations-item-' + post_id + ' .conversation-follower-list-item-' + data.success.user.id + ' .toggle-follower')
              .removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
            $('#conversations-item-' + post_id + ' .conversation-follower-list').append('' +
              '<a href="/list/' + data.success.workspace_id + '/' + data.success.user.id + '/0/0" ' +
              '  class="users-item users-item-small conversation-followers-item-' + data.success.user.id + '"' +
              '  data-user_fullname="' + data.success.user.fullname + '"' +
              '  data-user_department="' + data.success.user.department + '"' +
              '  data-user_office="' + data.success.user.office + '"' +
              '  style="background-color: ' + data.success.user.color + '; ' +
              '         margin-left: 0; ' +
              '         margin-right: 0; ' +
              '         background-image: ' + data.success.user.background_image + '"' +
              '>' +
              data.success.user.first_letters +
              '</a>');
          } else {
            if ($('#current-user').val() == user_id) {
              $('#conversations-item-' + post_id + ' .conversation-follow-button-block')
                .removeClass('follow-button-block-active')
                .attr('title', 'Подписаться');
            }
            $('#conversations-item-' + post_id + ' .conversation-follower-list-item-' + data.success.user.id + ' .toggle-follower')
              .addClass('fa-plus-square-o').removeClass('fa-minus-square-o');
            $('#conversations-item-' + post_id + ' .conversation-followers-item-' + data.success.user.id).detach();
          }
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleLikeAjax(post_id, type) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/likes/toggle/",
      dataType: "json",
      type    : 'POST',
      data    : {
        post_id: post_id,
        type   : type
      },
      success : function (data) {
        $('.loading').hide();
        if (type == 'task') {
          let task_likes_count = $('#task-likes-count-input').val();
          if (data.success.liked == 'liked') {
            task_likes_count++;
            $('#task-like-button').attr('title', 'Убрать из избранного');
            $('#task-like-button i').removeClass('fa-thumbs-o-up').addClass('fa-thumbs-up');
            $('#task-likes-count').text(task_likes_count);
            $('#task-likes-count-input').val(task_likes_count);
          } else {
            task_likes_count--;
            $('#task-like-button').attr('title', 'Добавить в избранное');
            $('#task-like-button i').addClass('fa-thumbs-o-up').removeClass('fa-thumbs-up');
            $('#task-likes-count').text(task_likes_count);
            $('#task-likes-count-input').val(task_likes_count);
          }
        }
        let likes_count = $('#' + type + '-likes-count-input-' + post_id).val();
        if (data.success.liked == 'liked') {
          likes_count++;
          $('#' + type + '-like-button-' + post_id).attr('title', 'Убрать из избранного');
          $('#' + type + '-like-button-' + post_id + ' i').removeClass('fa-thumbs-o-up').addClass('fa-thumbs-up');
          $('#' + type + '-likes-count-' + post_id).text(likes_count);
          $('#' + type + '-likes-count-input-' + post_id).val(likes_count);
        } else {
          likes_count--;
          $('#' + type + '-like-button-' + post_id).attr('title', 'Добавить в избранное');
          $('#' + type + '-like-button-' + post_id + ' i').addClass('fa-thumbs-o-up').removeClass('fa-thumbs-up');
          $('#' + type + '-likes-count-' + post_id).text(likes_count);
          $('#' + type + '-likes-count-input-' + post_id).val(likes_count);
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function togglePrivateAjax(id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/tasks/' + id + '/togglePrivate/',
      dataType: "json",
      type    : 'PUT',
      success : function (data) {
        $('.loading').hide();
        if (data.success.is_private == 1) {
          $('.private-button-text').text('Сделать публичной');
        } else {
          $('.private-button-text').text('Сделать приватной');
        }
        commentTemplate(data.success.comment);
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleSectionAjax(id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/tasks/' + id + '/toggleSection/',
      dataType: "json",
      type    : 'PUT',
      success : function (data) {
        $('.loading').hide();
        $('#edit-task-status-input').val(4);
        $('#complete-button-' + data.success.id)
          .removeClass('complete-status-0')
          .removeClass('complete-status-1')
          .removeClass('complete-status-2')
          .removeClass('complete-status-3')
          .removeClass('complete-status-4')
          .addClass('complete-status-' + data.success.status);
        $('#task-item-' + data.success.id)
          .removeClass('task-visibly-status-0')
          .removeClass('task-visibly-status-1')
          .removeClass('task-visibly-status-2')
          .removeClass('task-visibly-status-3')
          .removeClass('task-visibly-status-4')
          .addClass('task-visibly-status-' + data.success.status);
        if (data.success.status == 4) {
          $('#toggle-section-button').attr('title', 'Преобразовать в задачу');
          $('.toggle-section-button-text').text('Преобразовать в задачу');
        } else {
          $('#task-item-' + data.success.id)
          $('#toggle-section-button').attr('title', 'Преобразовать в секцию');
          $('.toggle-section-button-text').text('Преобразовать в секцию');
          $('#edit-project-status').addClass('edit-project-status').removeClass('edit-project-status-active');
          if (visibly_filter == 2) {
            $('#task-item-' + data.success.id).hide(800);
          }
        }
        commentTemplate(data.success.comment);
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleStatusAjax(task_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/tasks/' + task_id + '/toggleStatus/',
      dataType: "json",
      type    : 'PUT',
      success : function (data) {
        $('.loading').hide();
        if (data.success.parent_id) {
          getTaskAjax(data.success.parent_id);
        } else {
          $('.right-panel-edit').hide(300);
          $('.left-panel').removeClass('col-md-6').addClass('col-md-12');
        }
        $('#complete-button-' + data.success.id)
          .removeClass('complete-status-0')
          .removeClass('complete-status-1')
          .removeClass('complete-status-2')
          .removeClass('complete-status-3')
          .removeClass('complete-status-4')
          .removeClass('complete-status-5')
          .addClass('complete-status-' + data.success.status);
        if ($('#edit-task-id-input').val() == data.success.id) {
          commentTemplate(data.success.comment);
          switch (data.success.status) {
            case 1:
            case 4:
              $('#edit-project-status').addClass('edit-project-status').removeClass('edit-project-status-active');
              break;
            case 3:
            case 5:
              $('#edit-project-status').removeClass('edit-project-status').addClass('edit-project-status-active');
              break;
          }
        }
        if ($('#task-item-' + task_id).hasClass('subtasks-item')) {
          $('#task-item-' + data.success.id)
            .removeClass('subtask-status-1')
            .removeClass('subtask-status-3')
            .addClass('subtask-status-' + data.success.status);
          if ($('#subtasks-filter-radio-1').prop('checked')) {
            $('.subtask-status-3').hide(300);
          }
          if ($('#subtasks-filter-radio-2').prop('checked')) {
            $('.subtask-status-1').hide(300);
          }
        } else {
          $('#task-item-' + data.success.id)
            .removeClass('task-visibly-status-0')
            .removeClass('task-visibly-status-1')
            .removeClass('task-visibly-status-2')
            .removeClass('task-visibly-status-3')
            .removeClass('task-visibly-status-4')
            .removeClass('task-visibly-status-5')
            .addClass('task-visibly-status-' + data.success.status);
          if ((data.success.status == 1 || data.success.status == 4) && visibly_filter == 2) {
            $('#task-item-' + task_id).hide(800);
          }
          if ((data.success.status == 3 || data.success.status == 5) && visibly_filter == 1) {
            $('#task-item-' + task_id).hide(800);
          }
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function updateCommentAjax(comment_id, comment_text) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/comments/" + comment_id,
      dataType: "json",
      type    : "PUT",
      data    : {
        comment_text: comment_text
      },
      success : function (data) {
        $('.loading').hide();
        $('#edit-comment-dialog').hide();
        $('#comment-text-' + data.success.id).html(findLinks(data.success.text)).attr('data-content', data.success.text);
        $('#edit-comment-input-' + data.success.id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function updateProjectAjax(id, workspace_id, title, text, alias) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/projects/" + id,
      dataType: "json",
      type    : "PUT",
      data    : {
        workspace_id: workspace_id,
        title       : title,
        text        : text,
        alias       : alias
      },
      success : function (data) {
        $('.loading').hide();
        $('.projects-panel').show(300);
        $('.edit-project-panel').hide(300);
        $('#edit-project-dialog').hide(300);
        $('#project-title-' + data.success.id).text(data.success.title);
        $('#left-menu-project-' + data.success.id).text(data.success.title);
        $('#project-text-' + data.success.id).html(data.success.text);
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function updateTaskAjax(is_subtask) {
    let mentions = [];
    $('#quill-editor .ql-editor .mention').each(function (index, mention) {
      mentions.push($(mention).data('id'));
    });
    $('#edit-task-mentions').val(mentions);
    let msg = $('#task-edit-form').serialize();
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/tasks/" + $('#edit-task-id-input').val(),
      dataType: "json",
      type    : "PUT",
      data    : msg,
      success : function (data) {
        $('.loading').hide();
        let alias_string = '';
        if (data.success.project && data.success.project.alias) {
          alias_string = '<b>' + data.success.project.alias + ' </b>';
        }
        let title_string = 'Без названия';
        let class_string = '';
        if (data.success.title) {
          title_string = data.success.title;
        } else {
          class_string = 'empty-title';
        }
        $('#edit-task-title-input').attr('title', title_string);
        if (task_in_progress == 1 && !data.success.parent_id) {
          switch ($('#filter-type').val()) {
            case '0':
              appendNewTaskInMyTasks(data);
              break;
            case '1':
              appendNewTaskInProject(data);
              break;
          }
          setTimeout(function () {
            $('#task-created-label-' + data.success.id).hide(800)
          }, 2500);
          task_in_progress = 0;
          order_data = $('.tasks-body input').serialize();
          saveOrderAjax(order_data);
        }
        $('#task-link-' + data.success.id).html(alias_string + '<span class="' + class_string + '">' + title_string + '</span>');
        $('#task-item-' + data.success.id + ' .project-title').detach();
        $('.project-title-' + data.success.id).empty();
        if (data.success.project && $('#filter-type').val() == 0) {
          $('#task-item-' + data.success.id).append('' +
            '<div class="float-right project-title">\n' +
            '  <a href="/list/' + data.success.project.workspace_id + '/0/' + data.success.project.id + '/1" ' +
            '     style="background-color: ' + data.success.project.color + '"' +
            '  >\n' +
            data.success.project.title +
            '  </a>\n' +
            '</div>' +
            '');
          $('.project-title-' + data.success.id).html('' +
            '<span class="project-title">\n' +
            '  <a href="/list/' + data.success.project.workspace_id + '0/' + data.success.project.id + '/1" ' +
            '     style="background-color: ' + data.success.project.color + '"' +
            '  >\n' +
            data.success.project.title +
            '  </a>\n' +
            '</span>' +
            '');
        }
        $('#asignee-button-' + data.success.id)
          .css('backgroundColor', data.success.user.color)
          .css('backgroundImage', data.success.user.background_image)
          .text(data.success.user.first_letters)
          .attr('data-user_id', data.success.user.id)
          .attr('data-user_fullname', data.success.user.fullname)
          .attr('data-user_department', data.success.user.department)
          .attr('data-user_office', data.success.user.office)
        if (data.success.status == 3) {
          $('#complete-button-' + data.success.id).removeClass('complete-status-1').addClass('complete-status-3');
        } else {
          $('#complete-button-' + data.success.id).removeClass('complete-status-3').addClass('complete-status-1');
        }
        titleToEmptyLinks();
        if ($('#filter-type').val() == 0 && $('#user-id').val() != data.success.delegated_id) {
          $('#task-item-' + data.success.id).detach();
        }
        commentTemplate(data.success.comment);

        $('.followers-list').empty();
        $('.toggle-follower').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
        if (data.success.followers.length > 0) {
          for (var i = 0; i < data.success.followers.length; i++) {
            $('.task-followers-list').append('' +
              '<a href="/list/' + data.success.followers[i].workspace_id + '/' + data.success.followers[i].id + '/0/0" ' +
              'class="users-item task-followers-item-' + data.success.followers[i].id + '" ' +
              'data-user_fullname="' + data.success.followers[i].fullname + '" ' +
              'data-user_department="' + data.success.followers[i].department + '" ' +
              'data-user_office="' + data.success.followers[i].office + '" ' +
              'style="background-color: ' + data.success.followers[i].color + '; ' +
              '       margin-left: 0; ' +
              '       margin-right: 0;' +
              '       background-image: ' + data.success.followers[i].background_image +
              '">' +
              data.success.followers[i].first_letters +
              '</a>');
            $('#follower-list-item-' + data.success.followers[i].id + ' .toggle-follower')
              .addClass('fa-minus-square-o')
              .removeClass('fa-plus-square-o');
          }
        }

        if (is_subtask) {
          $('#edit-task-title-input').val('');
          $('#quill-editor-target-input').val('');
          $('#description-preview').empty();
          let project_id = $('#edit-task-project-input').val();
          let user_id = $('#user-id').val();
          let workspace_id = $('#workspace-id').val();
          let parent_id = $('#edit-task-id-input').val();
          createEmptyTaskAjax(project_id, user_id, workspace_id, 0, parent_id);
        }
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function uploadConversationAttachAjax(fd) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url        : "/api/uploadMessageAttach/",
      type       : "POST",
      data       : fd,
      processData: false,
      contentType: false,
      success    : function (response) {
        let data = JSON.parse(response);
        $('.loading').hide();
        $('#new-conversation-attachment-id-input').val(data.success.id);
        $('#new-conversation-attachment-block').empty().append('' +
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

//shared
function getTaskAjax(task_id) {
  $('.loading').show();
  ajaxSetup();
  $.ajax({
    url     : "/api/tasks/" + task_id,
    dataType: "json",
    type    : 'GET',
    success : function (data) {
      access_users_array.length = 0;
      $('.loading').hide();

      //clear

      $('.task-item').removeClass('task-selected').removeClass('btn-primary');
      $('#task-item-' + task_id).addClass('task-selected').addClass('btn-primary');
      $('#my_tasks-files').hide();
      $('#my_tasks-calendar').hide();
      $('#page-header-calendar').removeClass('page-header-submenu-active');
      $('#my_tasks-list').show();
      $('#page-header-list').addClass('page-header-submenu-active');
      $('#page-header-files').removeClass('page-header-submenu-active');
      $('.right-panel-edit').show();
      $('.right-panel').hide();
      $('.left-panel').removeClass('col-md-12').addClass('col-md-6');
      if (sidebar_opened) {
        $('.left-panel').css('width', '38%').css('left', '25%');
      } else if ($(window).width() > 600) {
        $('.left-panel').css('width', '50%').css('left', '0');
      }
      $('#edit-task-implementer').text(data.success.implementer_name).val(data.success.implementer_id);
      $('.edit-task-other-users').detach();
      for (var i = 0; i < data.success.users.length; i++) {
        if (data.success.users[i].id != data.success.implementer_id) {
          $('#edit-task-delegated-input').append('' +
            '<option class="edit-task-other-users" value="' + data.success.users[i].id + '">' +
            data.success.users[i].name +
            '</option>');
        }
      }
      $('#add-comment-recipient_id').val(data.success.implementer_id);
      let project_string = data.success.task.project ? data.success.task.project.title : "Добавить к проекту";
      $('.edit-add-task-to-project span').text(project_string);
      $('#edit-task-id-input').val(data.success.task.id);
      $('#edit-task-add-comment').val(data.success.task.id);
      $('#edit-task-delete-button').attr('data-id', data.success.task.id);
      $('.followers-dialog li').attr('data-post_id', data.success.task.id);
      $('.follow-button-block').attr('data-post_id', data.success.task.id);
      $('#edit-task-attach-task_id').val(data.success.task.id);
      $('#edit-task-project-input').val(data.success.task.project_id);
      $('.back-to-user-button')
        .attr('href', '/list/' + data.success.task.workspace_id + '/' + data.success.task.delegated_id + '/0/0/');
      if (data.success.task.project_id || data.success.task.project_id == 0) {
        $('.clear-project').show();
      } else {
        $('.clear-project').hide();
      }

      if (data.success.task.is_private == 1) {
        $('.private-button-text').text('Сделать публичной');
      } else {
        $('.private-button-text').text('Сделать приватной');
      }

      if (data.success.task.conversation) {
        $('#task-conversation-button').attr('data-conversation_id', data.success.task.conversation.id);
      } else {
        $('#task-conversation-button').attr('data-conversation_id', '');
      }

      $('#edit-task-workspace-input').val(data.success.task.workspace_id);
      $('#edit-task-status-input').val(data.success.task.status);
      $('#edit-task-due-date-input').val(data.success.task.due_date);
      if (!$('#edit-task-due-date-input').val() || $('#edit-task-due-date-input').val().trim() == '') {
        $('.clear-due_date').hide();
      } else {
        $('.clear-due_date').show();
      }
      switch (data.success.task.status) {
        case 0:
        case 1:
        case 2:
          $('#edit-project-status').addClass('edit-project-status').removeClass('edit-project-status-active');
          $('#toggle-section-button').attr('title', 'Преобразовать в секцию');
          $('.toggle-section-button-text').text('Преобразовать в секцию');
          break;
        case 3:
          $('#edit-project-status').removeClass('edit-project-status').addClass('edit-project-status-active');
          $('#toggle-section-button').attr('title', 'Преобразовать в секцию');
          $('.toggle-section-button-text').text('Преобразовать в секцию');
          break;
        case 4:
          $('#edit-project-status').addClass('edit-project-status').removeClass('edit-project-status-active');
          $('#toggle-section-button').attr('title', 'Преобразовать в задачу');
          $('.toggle-section-button-text').text('Преобразовать в задачу');
          break;
        case 5:
          $('#edit-project-status').removeClass('edit-project-status').addClass('edit-project-status-active');
          $('#toggle-section-button').attr('title', 'Преобразовать в задачу');
          $('.toggle-section-button-text').text('Преобразовать в задачу');
          break;
      }
      $('#edit-task-title-input').val(data.success.task.title).attr('title', data.success.task.title);
      $('#quill-editor .ql-editor').html(data.success.task.text);
      $('#task-description-block .ql-toolbar').hide();
      $('#task-description-block .ql-container').hide();
      $('#description-preview').empty().html(findLinks(data.success.task.text)).show();

      //fill parent task

      $('#parent-tasks-block').empty();
      if (data.success.task.parent_task) {
        let parent_title = 'Без названия';
        if (data.success.task.parent_task.title) {
          parent_title = data.success.task.parent_task.title;
        }
        $('#parent-tasks-block').append('' +
          '<div class="parent-tasks-item">' +
          '  <a class="task-link" data-id="' + data.success.task.parent_task.id + '">' +
          '    <i class="fa fa-arrow-circle-o-left" aria-hidden="true"> ' + parent_title +
          '  </a>' +
          '</div>');
        $('#select-project-block').hide();
        $('#create-subtask-button').hide();
        $('#tags-button').hide();
        $('#toggle-section-button').hide();
        $('#private-button').hide();
        $('#edit-task-delegated-input').hide();
        $('#task-item-' + data.success.task.parent_task.id).addClass('task-selected').addClass('btn-primary');
      } else {
        $('#select-project-block').show();
        $('#create-subtask-button').show();
        $('#tags-button').show();
        $('#toggle-section-button').show();
        $('#private-button').show();
        $('#edit-task-delegated-input').show();
      }

      //tags

      $('.tags-block').empty();
      for (var i = 0; i < data.success.task.tags.length; i++) {
        $('.tags-block').append('' +
          '<div class="tags-item task-tag"' +
          '     id="task-tag-' + data.success.task.tags[i].id + '"' +
          '     data-id="' + data.success.task.tags[i].id + '"' +
          '     style="background-color: ' + data.success.task.tags[i].color + '"' +
          '>' +
          '  <a href="/list/' + $('#current-workspace').val() + '/0/0/6/' + data.success.task.tags[i].id + '">' +
          '    <i class="fa fa-tag" aria-hidden="true"></i> ' + data.success.task.tags[i].title +
          '  </a>' +
          '  <span class="detach-tag crm-button" data-id="' + data.success.task.tags[i].id + '">' +
          '    <i class="fa fa-times" aria-hidden="true"></i>' +
          '  </span>' +
          '</div>' +
          '');
      }
      $('.tags-list').empty();
      for (var i = 0; i < data.success.all_tags.length; i++) {
        if (data.success.task.tags.indexOf(data.success.all_tags[i].id) == -1) {
          $('#tags-list').append('' +
            '<div class="tags-item tags-item-select"' +
            '     id="tag-' + data.success.all_tags[i].id + '"' +
            '     data-id="' + data.success.all_tags[i].id + '"' +
            '     style="background-color: ' + data.success.all_tags[i].color + '"' +
            '     title="Добавить к задаче"' +
            '>' +
            '  <span class="tags-item-select-title"' +
            '        data-id="' + data.success.all_tags[i].id + '"' +
            '  >' +
            '    <i class="fa fa-tag" aria-hidden="true"></i> ' + data.success.all_tags[i].title +
            '  </span>' +
            '  <span class="delete-tag crm-button"' +
            '        data-id="' + data.success.all_tags[i].id + '"' +
            '        title="Удалить метку"' +
            '  >' +
            '    <i class="fa fa-times" aria-hidden="true"></i>\n' +
            '  </span>' +
            '</div>' +
            '');
        }
      }

      //fill subtasks

      $('#subtasks-block').empty();
      if (data.success.task.subtasks && data.success.task.subtasks.length > 0) {
        $('#subtasks-block').append('' +
          '<div class="subtasks-filter-block">' +
          '  <div class="float-right crm-button" id="subtasks-filter-button">' +
          '    <i class="fa fa-filter" aria-hidden="true"></i>' +
          '  </div>' +
          '  <div class="dialog subtasks-filter-dialog">' +
          '    <ul>' +
          '      <li>' +
          '        <label>' +
          '          <input type="radio"' +
          '                 name="subtasks-filter-radio"' +
          '                 value="1" checked' +
          '                 id="subtasks-filter-radio-1"' +
          '          >' +
          '          Незавершенные задачи' +
          '        </label>' +
          '      </li>' +
          '      <li>' +
          '        <label>' +
          '          <input type="radio"' +
          '                 name="subtasks-filter-radio"' +
          '                 value="2"' +
          '                 id="subtasks-filter-radio-2"' +
          '          >' +
          '          Завершенные задачи' +
          '        </label>' +
          '      </li>' +
          '      <li>' +
          '        <label>' +
          '          <input type="radio" name="subtasks-filter-radio" value="3">' +
          '          Все задачи' +
          '        </label>' +
          '      </li>' +
          '    </ul>' +
          '  </div>' +
          '</div>' +
          '');
        for (var i = 0; i < data.success.task.subtasks.length; i++) {
          let title_string = 'Без названия';
          let class_string = '';
          if (data.success.task.subtasks[i].title) {
            title_string = data.success.task.subtasks[i].title;
          } else {
            class_string = 'empty-title';
          }
          let visibility_string = data.success.task.subtasks[i].status == 3 ? 'style="display: none"' : '';
          $('#subtasks-block').append('' +
            '<div class="subtasks-item task-item subtask-status-' + data.success.task.subtasks[i].status + '"' +
            '     id="task-item-' + data.success.task.subtasks[i].id + '"' +
            visibility_string +
            '>' +
            '  <input type="hidden" name="sort[]" value="' + data.success.task.subtasks[i].id + '">' +
            '  <span class="drag-button">' +
            '    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
            '    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
            '  </span>' +
            '  <span class="complete-button complete-status-' + data.success.task.subtasks[i].status + '"' +
            '        data-id="' + data.success.task.subtasks[i].id + '"' +
            '        id="complete-button-' + data.success.task.subtasks[i].id + '"' +
            '  >' +
            '    <i class="fa fa-check-circle-o" aria-hidden="true"></i>' +
            '  </span>' +
            '  <span class="task-title">' +
            '    <a href="#"' +
            '       class="task-link + ' + class_string + '"' +
            '       data-id="' + data.success.task.subtasks[i].id + '"' +
            '       id="task-link-' + data.success.task.subtasks[i].id + '"' +
            '    >' +
            title_string +
            '    </a>' +
            '   </span>' +
            '   <a class="float-right task-link text-right crm-button" ' +
            '      data-id="' + data.success.task.subtasks[i].id + '"' +
            '      title="Подробности и комметарии" ' +
            '   >' +
            '     <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i>' +
            '   </a>' +
            '</div>' +
            '');
        }
      }

      //fill files

      $('.edit-tasks-files').empty();
      if (data.success.files.length != 0) {
        for (var i = 0; i < data.success.files.length; i++) {
          let preview_string = '';
          if (data.success.files[i].type == 'jpeg' || data.success.files[i].type == 'jpg' || data.success.files[i].type == 'png'
            || data.success.files[i].type == 'gif') {
            preview_string = '<br/>' +
              '<div class="text-center">' +
              '  <img src="/download/' + data.success.files[i].id + '" ' +
              '     class="' + data.success.files[i].class + '" ' +
              '     data-url="/download/' + data.success.files[i].id + '"' +
              '     alt="' + data.success.files[i].alt + '"' +
              '     title="' + data.success.files[i].alt + '"' +
              '  >' +
              '</div>';
          }
          $('.edit-tasks-files').append('' +
            likeTemplate(data.success.files[i], 'file') +
            '<div class="file-link" id="small-file-item-' + data.success.files[i].id + '">' +
            '    <a href="/download/' + data.success.files[i].id + '">' +
            '      <img class="fileicon" src="/img/fileicons/' + data.success.files[i].type + '.png"> '
            + data.success.files[i].original_filename +
            '    </a>' +
            '    <span class="file-delete" data-id="' + data.success.files[i].id + '">\n' +
            '      <i class="fa fa-trash" aria-hidden="true"></i>\n' +
            '    </span>' +
            preview_string +
            '</div>'
          );
        }
      }

      //fill comments

      $('.edit-tasks-comments').empty();
      $('.hidden-comments-block').empty();
      if (data.success.comments.length != 0) {
        for (var i = 0; i < data.success.comments.length; i++) {
          console.log(data.success.comments[i]);
          if (data.success.comments[i].type != 'task_mention'
            && data.success.comments[i].type != 'comment_mention') {
            commentTemplate(data.success.comments[i])
          }
        }
      }
      hideComments();

      // followers

      if (data.success.is_follower == true) {
        $('.follow-button-block').addClass('follow-button-block-active').attr('data-task_id', data.success.task.id);
        $('.follow-button-text').text(' Вы подписаны');
      } else {
        $('.follow-button-block').removeClass('follow-button-block-active').attr('data-task_id', data.success.task.id);
        $('.follow-button-text').text(' Подписаться');
      }

      $('.task-followers-list').empty();
      $('.task-followers-list .toggle-follower').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
      if (data.success.followers.length > 0) {
        for (var i = 0; i < data.success.followers.length; i++) {
          $('.task-followers-list').append('' +
            '<a href="/list/' + data.success.followers[i].workspace_id + '/' + data.success.followers[i].id + '/0/0" ' +
            'class="users-item task-followers-item-' + data.success.followers[i].id + '" ' +
            'data-user_fullname="' + data.success.followers[i].fullname + '" ' +
            'data-user_department="' + data.success.followers[i].department + '" ' +
            'data-user_office="' + data.success.followers[i].office + '" ' +
            'style="background-color: ' + data.success.followers[i].color + '; ' +
            '       margin-left: 0; ' +
            '       margin-right: 0;' +
            '       background-image: ' + data.success.followers[i].background_image +
            '">' +
            data.success.followers[i].first_letters +
            '</a>');
          $('.task-followers-item-' + data.success.followers[i].id + ' .toggle-follower')
            .addClass('fa-minus-square-o')
            .removeClass('fa-plus-square-o');
        }
      }

      //likes
      if (data.success.task.is_liked == true) {
        $('#task-like-button').attr('title', 'Убрать из избранного');
        $('#task-like-button i').removeClass('fa-thumbs-o-up').addClass('fa-thumbs-up');
      } else {
        $('#task-like-button').attr('title', 'Добавить в избранное');
        $('#task-like-button i').addClass('fa-thumbs-o-up').removeClass('fa-thumbs-up');
      }
      $('#task-likes-count').text(data.success.task.likes_count);
      $('#task-likes-count-input').val(data.success.task.likes_count);
    },
    error   : function (jqxhr, status, errorMsg) {
      ajaxErrorsHandling(jqxhr, status, errorMsg);
    }
  });
}

function findLinks(inputText) {
  let replacedText, replacePattern1, replacePattern2, replacePattern3;
  let href_marker = '<a'

  if (inputText && inputText.indexOf(href_marker) == -1) {
    //URLs starting with http://, https://, or ftp://
    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    if (inputText) {
      replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');
    }

    //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
    replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
    if (inputText) {
      replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');
    }

    //Change email addresses to mailto:: links.
    replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
    if (inputText) {
      replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');
    }
  } else {
    replacedText = inputText;
  }

  $('.mention').each(function (index, item) {
    let user_id = $(item).attr('data-id');
    let workspace_id = $('#current-workspace').val();
    if (!$(item).parent().hasClass('mention-wrapper')) {
      $(item).wrap('<a href="/list/' + workspace_id + '/' + user_id + '/0/0/" class="mention-wrapper" />');
    }
  });

  return replacedText;
}

function restartPushServerAjax() {
  ajaxSetup();
  $.ajax({
    url     : "/api/restartPushServer/",
    dataType: "json",
    success : function (data) {
      console.log(data);
    },
    error   : function (jqxhr, status, errorMsg) {
      ajaxErrorsHandling(jqxhr, status, errorMsg);
    }
  });
}

function hideComments() {
  $('.hidden-comments-button').hide();
  let comments_count = $('.comment-item').length;
  for (var i = 0; i < comments_count - 5; i++) {
    $('.hidden-comments-block').append($('.comment-item').eq(i).detach());
  }
  $('.hidden-comments-block').hide();
  if (comments_count > 5) {
    $('.hidden-comments-button').show();
  }
}

function likeTemplate(object, type) {
  let like_title = object.is_liked ? 'Убрать из избранного' : 'Добавить в избранное';
  let like_class = object.is_liked ? 'fa-thumbs-up' : 'fa-thumbs-o-up';
  let template = '' +
    '<div class="' + type + '-like-button float-right crm-button like-button"' +
    '     id="' + type + '-like-button-' + object.id + '"' +
    '     data-id="' + object.id + '"' +
    '     data-type="' + type + '"' +
    '     title="' + like_title + '"' +
    '>' +
    '  <input type="hidden" ' +
    '         id="' + type + '-likes-count-input-' + object.id + '" ' +
    '         value="' + object.likes_count + '"' +
    '  >' +
    '    <i class="fa ' + like_class + '" aria-hidden="true"></i> ' +
    '    <span id="' + type + '-likes-count-' + object.id + '">' +
    object.likes_count +
    '    </span>' +
    '</div>';

  return template;
}

function commentTemplate(comment_object) {
  let author_string = '';
  let actions_string = '';
  let text_string = '';
  if (comment_object.text) {
    text_string = findLinks(comment_object.text);
  }
  let delete_button_string = '' +
    '<div class="comment-delete-button crm-button" data-id="' + comment_object.id + '">' +
    '  <i class="fa fa-trash" aria-hidden="true"></i>' +
    '</div>';
  let like_string = likeTemplate(comment_object, 'comment');
  let edit_button_string = '' +
    '<span class="comment-edit-button crm-button" data-id="' + comment_object.id + '">' +
    '  <i class="fa fa-pencil" aria-hidden="true"></i>' +
    '</span>';

  if (comment_object.author_id == null) {
    actions_string = delete_button_string + like_string;
  } else if (comment_object.author_id == $('#current-user').val()) {
    actions_string = delete_button_string + edit_button_string + like_string;
  } else {
    actions_string = like_string;
  }
  if (comment_object.author_id) {
    author_string = '<a href="/list/' + $('#workspace-id').val() + '/' + comment_object.author_id + '/0/0/">' + comment_object.author_name + '</a>';
  } else {
    author_string = comment_object.author_name;
  }
  $('.edit-tasks-comments').append('' +
    '<div class="comment-item ' + comment_object.class + '" ' +
    '     id="comment-item-' + comment_object.id + '"' +
    '>' +
    '  <div class="comment-header">' +
    comment_object.date +
    '    <b class="author-string">' + author_string + ': </b>' +
    actions_string +
    '  </div>' +
    '  <div class="comment-body">' +
    '    <span class="comment-text" id="comment-text-' +
    comment_object.id + '"' +
    '    >' +
    text_string +
    '    </span> ' +
    '  </div>' +
    '</div>');
  $('#comment-item-' + comment_object.id + ' .comment-text').attr('data-content', comment_object.text);
}