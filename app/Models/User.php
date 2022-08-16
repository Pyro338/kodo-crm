<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Helpers\HtmlHelper;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscribe',
        'role',
        'first_name',
        'middle_name',
        'last_name',
        'avatar',
        'office',
        'department',
        'phone',
        'about',
        'workspace_id',
        'workspaces',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_link',
        'additional_workspaces',
        'background_image',
        'color',
        'first_letters',
        'fullname',
        'link',
        'workspace',
        'workspaces_list',
    ];

    public function getAvatarLinkAttribute()
    {
        return $this->avatar ? '/download/' . $this->avatar : '/img/no-photo.png';
    }

    public function getLinkAttribute()
    {
        return HtmlHelper::getUserLink($this);
    }

    public function getBackgroundImageAttribute()
    {
        return $this->avatar ? 'url(/download/' . $this->avatar . ')' : 'url(/img/user-background.png)';
    }

    public function getColorAttribute()
    {
        return $this->avatar ? 'transparent' : HtmlHelper::userColor($this->name);
    }

    public function getFirstLettersAttribute()
    {
        return $this->avatar ? '' : self::firstLetters($this);
    }

    public function getFullnameAttribute()
    {
        if (trim($this->last_name . $this->first_name . $this->middle_name) != '') {
            $full_name = trim($this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name);
        } else {
            $full_name = $this->name;
        }

        return $full_name;
    }

    public function getAdditionalWorkspacesAttribute()
    {
        $additional_workspaces = (object)array();
        if ($this->workspaces) {
            $workspaces_ids = json_decode($this->workspaces, true);
            foreach ($workspaces_ids as $key => $workspace_id) {
                $additional_workspaces->$key = Workspace::find($workspace_id);
            }
        }

        return $additional_workspaces;
    }

    public function workspace()
    {
        return $this->hasOne(Workspace::class, 'id', 'workspace_id');

    }

    public function getWorkspaceAttribute()
    {
        return $this->workspace()->first();
    }

    public function getWorkspacesListAttribute()
    {
        $workspaces = [];
        if ($this->workspaces) {
            $workspaces = json_decode($this->workspaces, true);
        }

        array_push($workspaces, (string)$this->workspace_id);

        return $workspaces;
    }

    //

    public static function commentNotification($user_id)
    {
        $notified_user = User::find($user_id);
        if ($notified_user->unread_messages < 5) {
            $notified_user->unread_messages++;
            $notified_user->save();
        }

        return $notified_user;
    }

    public static function firstLetters($user)
    {
        if ($user->first_name) {
            if ($user->last_name) {
                $first_letters = mb_substr($user->first_name, 0, 1) . mb_substr($user->last_name, 0, 1);
            } else {
                $first_letters = mb_substr($user->first_name, 0, 2);
            }
        } else {
            $first_letters = mb_substr($user->name, 0, 2);
        }

        return $first_letters;
    }

    public static function getRoleName($user)
    {
        switch ($user->role) {
            case 'superadmin' :
                $role_name = 'Management';
                break;
            case 'user' :
                $role_name = 'Technology';
                break;
            case 'operator' :
                $role_name = 'Operations';
                break;
            default:
                $role_name = '';
                break;
        }

        return $role_name;
    }
}
