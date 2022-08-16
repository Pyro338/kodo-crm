<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\CreatesUser;
use Tests\CreatesWorkspace;
use Tests\CreatesConversation;
use Tests\CreatesProject;
use Tests\CreatesTask;

class PagesTest extends TestCase
{
    use CreatesUser, CreatesWorkspace, CreatesConversation, CreatesProject, CreatesTask;

    public function testGuest()
    {
        echo('Testing guest' . PHP_EOL);

        $response = $this->get('/');
        $response->assertStatus(302);
    }

    public function testWelcome()
    {
        echo('Testing welcome page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }

    public function testConversations()
    {
        echo('Testing conversations page' . PHP_EOL);

        $workspace    = $this->createWorkspace();
        $user         = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $conversation = $this->createConversation(
            [
                'workspace_id' => $workspace->id,
                'owner_id'     => $user->id
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/conversations/' . $workspace->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/conversations/' . $workspace->id . '/' . $conversation->id);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $conversation->delete();
    }

    public function testFavorites()
    {
        echo('Testing favorites page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/favorites/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }

    public function testInbox()
    {
        echo('Testing inbox page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/inbox/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }

    public function testList()
    {
        echo('Testing list page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $project   = $this->createProject(
            [
                'owner_id'     => $user->id,
                'workspace_id' => $workspace->id
            ]
        );
        $task      = $this->createTask(
            [
                'delegated_id'   => $user->id,
                'implementer_id' => $user->id,
                'owner_id'       => $user->id,
                'project_id'     => $project->id,
                'workspace_id'   => $workspace->id
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/0/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/1/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/2/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/3/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/4/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/5/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/list/' . $workspace->id . '/' . $user->id . '/' . $project->id . '/0/' . $task->id);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
    }

    public function testPersonal()
    {
        echo('Testing personal page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/personal/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }

    public function testProjects()
    {
        echo('Testing projects page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/projects/' . $workspace->id . '/0/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/projects/' . $workspace->id . '/1/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }

    public function testSearch()
    {
        echo('Testing search page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/search/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }

    public function testUsers()
    {
        echo('Testing users page' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        auth()->login($user);
        $response = $this->actingAs($user)->get('/users/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
    }
}
