<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get(
    '/home/',
    function () {
        return redirect('/');
    }
);

Auth::routes();
Route::get('/logout/', 'Auth\LoginController@logout')->name('logout');

//pages routes
Route::get('/', 'PagesController@welcome')->name('welcome');
Route::get('/conversations/{workspace}/{conversation?}', 'PagesController@conversations')->name('conversations');
Route::get('/favorites/', 'PagesController@favorites')->name('favorites');
Route::get('/inbox/', 'PagesController@inbox')->name('inbox');
Route::get('/list/{workspace_id}/{user_id}/{project_id}/{filter_type}/{post_id?}', 'PagesController@tasksList')->name('list');
Route::get('/personal/', 'PagesController@personal')->name('personal');
Route::get('/projects/{workspace}/{filter_type}', 'PagesController@projects')->name('projects');
Route::get('/search/', 'PagesController@search')->name('search');
Route::get('/users/', 'InviteController@index')->name('usersIndex');

//service routes
Route::get('/download/{id}', 'FileController@download')->name('download');
Route::get('/invtersOnly/', 'InviteController@invitesonly')->name('invtersOnly');
Route::get('/usersCreate/', 'InviteController@create')->name('usersCreate');;
Route::get('/usersStore/', 'InviteController@usersStore')->name('usersStore');

//api
Route::group(['middleware' => ['auth'], 'prefix' => '/api'], function () {
    Route::resource('comments', 'Api\CommentController');
    Route::put('/comments/{comment}/toggleArchive/', 'Api\CommentController@toggleArchive')->name('comments.toggleArchive');
    Route::post('/comments/allToArchive/', 'Api\CommentController@allToArchive')->name('comments.allToArchive');
    Route::post('/comments/clearArchive/', 'Api\CommentController@clearArchive')->name('comments.clearArchive');

    Route::resource('conversations', 'Api\ConversationController');

    Route::resource('files', 'Api\FileController');

    Route::post('/followers/toggle/', 'Api\FollowerController@toggle')->name('followers.toggle');

    Route::post('/likes/toggle/', 'Api\LikeController@toggle')->name('likes.toggle');

    Route::resource('messages', 'Api\MessageController');
    Route::post('/messages/attach/', 'Api\MessageController@attach')->name('messages.attach');

    Route::resource('projects', 'Api\ProjectController');
    Route::put('/projects/{project}/restore', 'Api\ProjectController@restore')->name('projects.restore');

    Route::resource('tags', 'Api\TagController');

    Route::resource('tasks', 'Api\TaskController');
    Route::put('/tasks/{task}/restore', 'Api\TaskController@restore')->name('tasks.restore');
    Route::post('/tasks/createEmpty/', 'Api\TaskController@createEmpty')->name('tasks.createEmpty');
    Route::post('/tasks/saveOrder/', 'Api\TaskController@saveOrder')->name('tasks.saveOrder');
    Route::put('/tasks/{task}/addTag/', 'Api\TaskController@addTag')->name('tasks.addTag');
    Route::put('/tasks/{task}/removeTag/', 'Api\TaskController@removeTag')->name('tasks.removeTag');
    Route::put('/tasks/{task}/assign/', 'Api\TaskController@assign')->name('tasks.assign');
    Route::put('/tasks/{task}/changeTimeMark/', 'Api\TaskController@changeTimeMark')->name('tasks.changeTimeMark');
    Route::put('/tasks/{task}/togglePrivate/', 'Api\TaskController@togglePrivate')->name('tasks.togglePrivate');
    Route::put('/tasks/{task}/toggleSection/', 'Api\TaskController@toggleSection')->name('tasks.toggleSection');
    Route::put('/tasks/{task}/toggleStatus/', 'Api\TaskController@toggleStatus')->name('tasks.toggleStatus');
    Route::post('/tasks/getCalendarEvents/', 'Api\TaskController@getCalendarEvents')->name('tasks.getCalendarEvents');

    Route::resource('users', 'Api\UserController');
    Route::post('/users/changePassword/', 'Api\UserController@changePassword')->name('users.changePassword');
    Route::put('/users/{user}/changeProperty/', 'Api\UserController@changeProperty')->name('users.changeProperty');
    Route::post('/users/checkEmailUnique/', 'Api\UserController@checkEmailUnique')->name('users.checkEmailUnique');
    Route::post('/users/deleteAvatar/', 'Api\UserController@deleteAvatar')->name('users.deleteAvatar');
    Route::post('/users/toggleConversationSubscribe/', 'Api\UserController@toggleConversationSubscribe')->name('users.toggleConversationSubscribe');
    Route::post('/users/toggleEnableSound/', 'Api\UserController@toggleEnableSound')->name('users.toggleEnableSound');
    Route::post('/users/toggleSubscribe/', 'Api\UserController@toggleSubscribe')->name('users.toggleSubscribe');
    Route::put('/users/{user}/updatePersonal/', 'Api\UserController@updatePersonal')->name('users.updatePersonal');
    Route::post('/users/uploadAvatar/', 'Api\UserController@uploadAvatar')->name('users.uploadAvatar');
    Route::put('/users/{user}/toggleWorkspaceAccess/', 'Api\UserController@toggleWorkspaceAccess')->name('users.toggleWorkspaceAccess');

    Route::resource('workspaces', 'Api\WorkspaceController');
//artisan routes
    //TODO думаю, для artisan routes существует отдельный файл - console.php
    //эта функция вызывается аяксом
    Route::get('/restartPushServer/', function () {
        $restart = Artisan::call('push_server:serve');

        if ($restart) {
            echo json_encode(['success' => 'Server restarted']);
        } else {
            echo json_encode(['fail' => 'Error occurred']);
        }
    });
});