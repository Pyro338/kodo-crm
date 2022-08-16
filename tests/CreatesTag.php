<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 15:37
 */

namespace Tests;

use App\Models\Tag;

trait CreatesTag
{
    public function createTag($attributes = [])
    {
        return factory(Tag::class)->create($attributes);
    }
}