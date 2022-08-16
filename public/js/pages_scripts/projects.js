$(document).ready(function ($) {
  let body = $('body');

  body.on('click', '.close-edit-project', function () {
    $('.projects-panel').show(300);
    $('.edit-project-panel').hide(300);
  });

  body.on('click', '#delete-project-submit', function () {
    if (confirm('Удалить?')) {
      let project_id = $('#edit-project-id').val();
      deleteProjectAjax(project_id);
    }
  });

  body.on('click', '.edit-project', function () {
    let project_id = $(this).attr('data-id');
    getProjectAjax(project_id);
  });

  body.on('click', '.restore-project', function () {
    let project_id = $(this).attr('data-id');
    restoreProjectAjax(project_id);
  });

  //projects page functions

  function deleteProjectAjax(project_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/projects/" + project_id,
      dataType: "json",
      type    : 'DELETE',
      success : function () {
        $('.loading').hide();
        $('.projects-panel').show();
        $('.edit-project-panel').hide();
        $('#project-item-' + project_id).detach();
        $('#left-menu-project-' + project_id).parent().detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function getProjectAjax(project_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/projects/" + project_id,
      dataType: "json",
      type    : 'GET',
      success : function (data) {
        $('.loading').hide();
        $('.projects-panel').hide(300);
        $('.edit-project-panel').show(300);
        $('#edit-project-workspace_id').val(data.workspace_id);
        $('#edit-project-title').val(data.title);
        $('#edit-project-text-quill-editor .ql-editor').html(data.text);
        $('#edit-project-id').val(data.id);
        $('#edit-project-alias').val(data.alias);
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function restoreProjectAjax(project_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/projects/" + project_id + '/restore/',
      dataType: "json",
      type    : 'PUT',
      success : function () {
        $('.loading').hide();
        $('#project-item-' + project_id).detach();
        $('#left-menu-project-' + project_id).detach();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  //paginate

  let inProgress = false; //флаг для отслеживания того, происходит ли в данный момент ajax-запрос
  let count = 2; //начинать пагинацию со 2 страницы, т.к. первая выводится сразу
  let countPage = $('#id-token').attr("data-last-page"); //токен для проверки запроса
  let block = $(".projects-block").get(0); //таблица, в которую будут добавляться строки
  $('#show-more-projects').click(function () {
    if (!inProgress && count <= countPage) {
      let data = {};
      data["token"] = $('#id-token').attr("data-token");
      data["page"] = count;
      $.ajax({
        url       : '',
        data      : data,
        type      : 'get',
        headers   : {
          'X-CSRF-TOKEN': data["token"]
        },
        /* выполнить до отправки запрса */
        beforeSend: function () {
          inProgress = true;
        },
        // Ответ от сервера
        success   : function (html) {
          $('.projects-block').append(html);
        },
        // Ошибка AJAX
        error     : function (result) {
          console.log(result);
        }
        /* сразу после выполнения запроса */
      }).done(function (data) {
        inProgress = false;
        count++;
      });
    }
  });
});