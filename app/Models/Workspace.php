<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 07.06.2018
 * Time: 12:02
 */

namespace App\Models;

class Workspace extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_visible'
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'workspace_id', 'id');
    }

    public function getConversationsAttribute()
    {
        return $this->conversations()
            ->where('is_visible', 1)
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}