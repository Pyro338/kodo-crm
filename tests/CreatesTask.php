<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 16:02
 */

namespace Tests;

use App\Models\Task;

trait CreatesTask
{
    public function createTask($attributes = [])
    {
        return factory(Task::class)->create($attributes);
    }
}