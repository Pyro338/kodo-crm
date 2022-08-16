<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $users = User::where('is_active', 1)->get();
        foreach ($users as $user) {
            $user->value = $user->name;
        }

        if ($users) {
            return ['success' => $users];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function store(Request $request)
    {
        $password                 = uniqid(rand(), true);
        $message_to_user          = $request->message;
        $create_data              = $request->all();
        $create_data['password']  = password_hash($password, PASSWORD_DEFAULT);
        $create_data['is_active'] = 1;
        $user                     = User::create($create_data);
        MailController::inviteMessage($user->email, $user, $password, $message_to_user);

        $user->message = 'Приглашение было удачно отправлено на адрес: ' . $user->email;

        if ($user) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  User                     $user
     *
     * @return array
     */
    public function update(User $user, Request $request)
    {
        $user->role = $request->user_role;
        $user->save();

        if ($user) {
            return ['success' => $user->role];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     *
     * @return array
     */
    public function destroy(User $user)
    {
        $user->is_active = 0;

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function changePassword(Request $request)
    {
        $user = User::find(Auth::user()->id);
        if (password_verify($request->password, $user->password)) {
            $user->password = password_hash($request->password, PASSWORD_DEFAULT);
            $user->save();
            $user->password_changed = true;
        } else {
            $user->password_changed = false;
        }

        if ($user) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function changeProperty(User $user, Request $request)
    {
        switch ($request->key) {
            case 'role':
                $user->role = $request->value;
                break;
            case 'department':
                $user->workspace_id = $request->value;
                $user->department   = Workspace::find($user->workspace_id)->title;
                break;
            case 'office':
                $user->office = $request->value;
                break;
        }

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function checkEmailUnique(Request $request)
    {
        if (User::where('email', $request->email)->first()) {
            $result['unique'] = false;
        } else {
            $result['unique'] = true;
        }

        return ['success' => $result];
    }

    public function deleteAvatar()
    {
        $user         = User::find(Auth::user()->id);
        $user->avatar = null;

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function toggleConversationSubscribe()
    {
        $user = User::find(Auth::user()->id);
        if ($user->conversations_subscribe == '1') {
            $user->conversations_subscribe = 0;
        } else {
            $user->conversations_subscribe = 1;
        }

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function toggleEnableSound()
    {
        $user = User::find(Auth::user()->id);
        if ($user->enable_sound == '1') {
            $user->enable_sound = 0;
        } else {
            $user->enable_sound = 1;
        }

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function toggleSubscribe()
    {
        $user = User::find(Auth::user()->id);
        if ($user->subscribe == '1') {
            $user->subscribe = 0;
        } else {
            $user->subscribe = 1;
        }

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function updatePersonal(User $user, Request $request)
    {
        $user->fill($request->all());

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function uploadAvatar(Request $request)
    {
        $new_file = File::createFile($request, $request->attachment);

        $user         = User::find(Auth::user()->id);
        $user->avatar = $new_file->id;
        $user->save();

        if ($new_file) {
            return ['success' => $new_file];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function toggleWorkspaceAccess(User $user, Request $request)
    {
        if ($user->workspaces) {
            $workspaces_ids = json_decode($user->workspaces);
            $key            = array_search($request->workspace_id, $workspaces_ids);
            if ($key !== false) {
                array_splice($workspaces_ids, $key, 1);
            } else {
                array_push($workspaces_ids, $request->workspace_id);
            }
            $user->workspaces = json_encode($workspaces_ids);
        } else {
            $new_workspaces_array = [];
            array_push($new_workspaces_array, $request->workspace_id);
            $user->workspaces = json_encode($new_workspaces_array);
        }

        if ($user->save()) {
            return ['success' => $user];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
