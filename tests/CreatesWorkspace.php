<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 15:16
 */

namespace Tests;

use App\Models\Workspace;

trait CreatesWorkspace
{
    public function createWorkspace($attributes = [])
    {
        return factory(Workspace::class)->create($attributes);
    }
}