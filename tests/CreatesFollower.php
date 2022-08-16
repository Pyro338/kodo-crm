<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 15:37
 */

namespace Tests;

use App\Models\Follower;

trait CreatesFollower
{
    public function createFollower($attributes = [])
    {
        return factory(Follower::class)->create($attributes);
    }
}