<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workspace;

class InviteController extends Controller
{
    public function index()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->role_name       = User::getRoleName($user);
            $user->role_name       = (trim($user->role_name) != '') ? $user->role_name : 'Роль не указана';
            $user->department      = (trim($user->department) != '') ? $user->department : 'Отдел не указан';
            $user->office          = (trim($user->office) != '') ? $user->office : 'Должность не указана';
            $user->workspaces_info = Workspace::where('is_visible', 1)->get();
            foreach ($user->workspaces_info as $workspace_info) {
                if ($workspace_info->id == $user->workspace_id) {
                    $workspace_info->item_class = 'disabled';
                }
                if ($user->workspaces) {
                    $workspaces_ids = json_decode($user->workspaces);
                    if (in_array($workspace_info->id, $workspaces_ids)) {
                        $workspace_info->toggle_class = 'fa-minus-square-o';
                    } else {
                        $workspace_info->toggle_class = 'fa-plus-square-o';
                    }
                } else {
                    $workspace_info->toggle_class = 'fa-plus-square-o';
                }
            }
        }
        $workspaces = Workspace::where('is_visible', 1)->get();

        return view(
            'users.users',
            [
                'users'      => $users,
                'workspaces' => $workspaces
            ]
        );
    }

    /**
     *
     * Метод возвращает view с формой создания инвайта
     *
     */
    public function create()
    {
        $workspaces = Workspace::where('is_visible', 1)->get();

        return view(
            'users.create',
            [
                'workspaces' => $workspaces
            ]
        );
    }

    public function usersStore(Request $request)
    {
        $password           = uniqid(rand(), true);
        $message_to_user    = $request->message;
        $user               = new User;
        $user->name         = $request->name;
        $user->last_name    = $request->last_name;
        $user->first_name   = $request->first_name;
        $user->middle_name  = $request->middle_name;
        $user->email        = $request->email;
        $user->phone        = $request->phone;
        $user->role         = $request->role;
        $user->department   = $request->department;
        $user->office       = $request->office;
        $user->workspace_id = $request->workspace_id;
        $user->password     = password_hash($password, PASSWORD_DEFAULT);
        $user->save();
        MailController::inviteMessage($user->email, $user, $password, $message_to_user);

        $message = 'Приглашение было удачно отправлено на адрес: ' . $user->email;

        return redirect('usersCreate')->with('message', $message);
    }

    /**
     * Метод возвращает view для тех, у кого нет инвайта
     */
    public function invitesonly()
    {
        return view('users.invitesonly');
    }

    /**
     * Конструктор
     *  - добавим Auth middleware, который позволяет нам легко и просто запретить доступ
     *    неавторизованным пользователям
     *  - и тут же добавим исключение: метод invitesonly() должен быть доступен всем.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'invitesonly']);
    }
}
