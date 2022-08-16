<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 21.11.2018
 * Time: 14:28
 */

namespace Tests;

use App\Models\User;

trait CreatesUser
{
    public function createRoleSuperdmin()
    {
        return $this->createUser(['role' => 'superadmin']);
    }

    public function createRoleManager()
    {
        return $this->createUser(['role' => 'manager']);
    }

    public function createRoleUser()
    {
        return $this->createUser(['role' => 'user']);
    }

    public function createRoleOperator()
    {
        return $this->createUser(['role' => 'operator']);
    }

    public function createUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }
}