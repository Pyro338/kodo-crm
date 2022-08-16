<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 15:37
 */

namespace Tests;

use App\Models\Comment;

trait CreatesComment
{
    public function createComment($attributes = [])
    {
        return factory(Comment::class)->create($attributes);
    }
}