<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 29.11.2018
 * Time: 16:02
 */

namespace Tests;

use App\Models\Project;

trait CreatesProject
{
    public function createProject($attributes = [])
    {
        return factory(Project::class)->create($attributes);
    }
}