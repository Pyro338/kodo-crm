$(document).ready(function ($) {
  let body = $('body');

  $('#all-to-arhive').click(function (e) {
    e.preventDefault();
    let coments_ids = [];
    $('.comments-active-body .comment-item').each(function () {
      coments_ids.push($(this).attr('data-id'));
    });
    allToArchiveAjax(coments_ids);
  });

  body.on('change', '#subscribe-radio', function () {
    toggleSubscribeAjax();
  });

  body.on('click', '#clear-archive', function () {
    if (confirm('Отчистить?')) {
      clearArchiveAjax();
    }
  });

  body.on('click', '.move-to-archive', function () {
    let comment_id = $(this).attr('data-id');
    toggleArchiveCommentAjax(comment_id);
  });

  //inbox page functions

  function allToArchiveAjax(comments_ids) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/comments/allToArchive/",
      dataType: "json",
      type    : 'POST',
      data    : {
        comments_ids: comments_ids
      },
      success : function () {
        $('.loading').hide();
        $('.comments-active-body .comment-item').each(function () {
          let comment_id = $(this).attr('data-id');
          $('#move-to-archive-' + comment_id).hide();
          $('#move-to-active-' + comment_id).show();
          $('.comments-arhive-body').prepend($('#inbox-item-' + comment_id).detach());
        });
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function clearArchiveAjax() {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : "/api/comments/clearArchive/",
      dataType: "json",
      type    : 'POST',
      success : function () {
        $('.loading').hide();
        $('.comments-arhive-body').empty();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  function toggleArchiveCommentAjax(comment_id) {
    $('.loading').show();
    ajaxSetup();
    $.ajax({
      url     : '/api/comments/' + comment_id + '/toggleArchive/',
      dataType: "json",
      type    : 'PUT',
      success : function (data) {
        $('.loading').hide();
        if (data.success.is_arhive == 1) {
          $('#move-to-archive-' + comment_id).hide();
          $('#move-to-active-' + comment_id).show();
          $('.comments-arhive-body').prepend($('#inbox-item-' + comment_id).detach());
        } else {
          $('#move-to-active-' + comment_id).hide();
          $('#move-to-archive-' + comment_id).show();
          $('.comments-active-body').prepend($('#inbox-item-' + comment_id).detach());
        }
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
      url     : "/api/toggleSubscribe/",
      dataType: "json",
      success : function () {
        $('.loading').hide();
      },
      error   : function (jqxhr, status, errorMsg) {
        ajaxErrorsHandling(jqxhr, status, errorMsg);
      }
    });
  }

  //paginate

  let inProgress = false; //флаг для отслеживания того, происходит ли в данный момент ajax-запрос
  let ActiveCount = 2; //начинать пагинацию со 2 страницы, т.к. первая выводится сразу
  let ArchiveCount = 2;
  let ActiveCountPage = $('#id-token-active').attr("data-last-page"); //токен для проверки запроса
  let ArchiveCountPage = $('#id-token-archive').attr("data-last-page");
  let block;
  let tab;
  /* Обработчик скролла страницы */
  $('.left-panel').scroll(function () {
    if ($(".comments-active-body").is(":visible")) {
      block = $(".comments-active-body").get(0);
      tab = 'active';
      console.log(block.clientHeight);
      if ((block.scrollHeight - block.scrollTop === block.clientHeight) && !inProgress && ActiveCount <= ActiveCountPage) {
        let data = {};
        data["token"] = $('#id-token').attr("data-token");
        data["page"] = ActiveCount;
        data["tab"] = tab;
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
            $('.comments-active-body').append(html);
          },
          // Ошибка AJAX
          error     : function (result) {
            console.log(result);
          }
          /* сразу после выполнения запроса */
        }).done(function (data) {
          inProgress = false;
          ActiveCount++;
        });
      }
    } else {
      block = $(".comments-arhive-body").get(0);
      tab = 'archive';
      if ((block.scrollHeight - block.scrollTop === block.clientHeight) && !inProgress && ArchiveCount <= ArchiveCountPage) {
        let data = {};
        data["token"] = $('#id-token').attr("data-token");
        data["page"] = ArchiveCount;
        data["tab"] = tab;
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
            $('.comments-arhive-body').append(html);
          },
          // Ошибка AJAX
          error     : function (result) {
            console.log(result);
          }
          /* сразу после выполнения запроса */
        }).done(function (data) {
          inProgress = false;
          ArchiveCount++;
        });
      }
    }
  });
});