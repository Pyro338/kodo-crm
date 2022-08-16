<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 15:37
 */

namespace Tests;

use App\Models\Conversation;

trait CreatesConversation
{
    public function createConversation($attributes = [])
    {
        return factory(Conversation::class)->create($attributes);
    }
}