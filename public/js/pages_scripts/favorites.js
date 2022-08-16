let body = $('body');

$('#favorite-tasks-button').click(function () {
  $('.page-header-submenu').removeClass('page-header-submenu-active');
  $(this).addClass('page-header-submenu-active');
  $('.favorites-panel').hide(300);
  $('#favorite-tasks').show(300);
});

$('#favorite-files-button').click(function () {
  $('.page-header-submenu').removeClass('page-header-submenu-active');
  $(this).addClass('page-header-submenu-active');
  $('.favorites-panel').hide(300);
  $('#favorite-files').show(300);
});

$('#favorite-comments-button').click(function () {
  $('.page-header-submenu').removeClass('page-header-submenu-active');
  $(this).addClass('page-header-submenu-active');
  $('.favorites-panel').hide(300);
  $('#favorite-comments').show(300);
});

$('#favorite-projects-button').click(function () {
  $('.page-header-submenu').removeClass('page-header-submenu-active');
  $(this).addClass('page-header-submenu-active');
  $('.favorites-panel').hide(300);
  $('#favorite-projects').show(300);
});

$('#favorite-conversations-button').click(function () {
  $('.page-header-submenu').removeClass('page-header-submenu-active');
  $(this).addClass('page-header-submenu-active');
  $('.favorites-panel').hide(300);
  $('#favorite-conversations').show(300);
});

$('#favorite-messages-button').click(function () {
  $('.page-header-submenu').removeClass('page-header-submenu-active');
  $(this).addClass('page-header-submenu-active');
  $('.favorites-panel').hide(300);
  $('#favorite-messages').show(300);
});

body.on('click', '.img-tile', function () {
  let img_url = $(this).attr('data-url');
  showImageModal(img_url);
});

function showImageModal(img_url) {
  $('#image-modal-image').attr('src', img_url);
  $('#image-modal').show();
}