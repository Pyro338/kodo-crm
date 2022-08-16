<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Message;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\FileBag;
use Tests\TestCase;
use Tests\CreatesUser;
use Tests\CreatesWorkspace;
use Tests\CreatesConversation;
use Tests\CreatesProject;
use Tests\CreatesTask;
use Tests\CreatesComment;
use Tests\CreatesFollower;
use Tests\CreatesTag;

class ApiTest extends TestCase
{
    use CreatesUser, CreatesWorkspace, CreatesConversation, CreatesProject, CreatesTask, CreatesComment, CreatesFollower, CreatesTag;

    public function testComments()
    {
        echo('Testing comments api' . PHP_EOL);

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
        $comment   = $this->createComment(
            [
                'task_id'      => $task->id,
                'author_id'    => $user->id,
                'recipient_id' => $user->id
            ]
        );
        $request   = [
            'task_id'      => $task->id,
            'text'         => 'lorem ipsum',
            'comment_text' => 'lorem ipsum dolor'
        ];
        auth()->login($user);
        $response    = $this->actingAs($user)->post('/api/comments', $request);
        $new_comment = Comment::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/comments/' . $comment->id, $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/comments/' . $comment->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/comments/' . $comment->id . '/toggleArchive/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/comments/allToArchive/', ['comments_ids' => [$comment->id]]);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/comments/clearArchive/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $task->delete();
        $comment->delete();
        $new_comment->delete();
    }

    public function testConversations()
    {
        echo('Testing conversations api' . PHP_EOL);

        $workspace    = $this->createWorkspace();
        $user         = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $project      = $this->createProject(
            [
                'owner_id'     => $user->id,
                'workspace_id' => $workspace->id
            ]
        );
        $task         = $this->createTask(
            [
                'delegated_id'   => $user->id,
                'implementer_id' => $user->id,
                'owner_id'       => $user->id,
                'project_id'     => $project->id,
                'workspace_id'   => $workspace->id
            ]
        );
        $conversation = $this->createConversation(
            [
                'workspace_id' => $workspace->id,
                'owner_id'     => $user->id
            ]
        );
        $request      = [
            'task_id'      => $task->id,
            'message_text' => 'lorem ipsum',
            'owner_id'     => $user->id
        ];
        auth()->login($user);
        $response         = $this->actingAs($user)->post('/api/conversations', $request);
        $new_conversation = Conversation::find($response->baseResponse->original['success']['conversation']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/conversations/' . $conversation->id);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $task->delete();
        $conversation->delete();
        $new_conversation->delete();
    }

    public function testFiles()
    {
        echo('Testing files api' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $task      = $this->createTask(
            [
                'delegated_id'   => $user->id,
                'implementer_id' => $user->id,
                'owner_id'       => $user->id,
                'workspace_id'   => $workspace->id
            ]
        );
        Storage::fake('attachments');
        $response = $this->actingAs($user)->json(
            'POST',
            '/api/files/',
            [
                'attachments'  => UploadedFile::fake()->create('avatar.jpg', 100),
                'task_id'      => $task->id,
                'workspace_id' => $workspace->id
            ]
        );
        $file     = File::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->json(
            'Delete',
            '/api/files/' . $file->id
        );
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $task->delete();
        $file->delete();
    }

    public function testFollowers()
    {
        echo('Testing followers api' . PHP_EOL);

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
        $request   = [
            'post_id' => $task->id,
            'type'    => 'task',
            'user_id' => $user->id
        ];
        auth()->login($user);
        $response = $this->actingAs($user)->post('/api/followers/toggle/', $request);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $task->delete();
    }

    public function testLikes()
    {
        echo('Testing likes api' . PHP_EOL);

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
        $request   = [
            'post_id' => $task->id,
            'type'    => 'task'
        ];
        auth()->login($user);
        $response = $this->actingAs($user)->post('/api/likes/toggle/', $request);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $task->delete();
    }

    public function testMessages()
    {
        echo('Testing messages api' . PHP_EOL);

        $workspace    = $this->createWorkspace();
        $user         = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $project      = $this->createProject(
            [
                'owner_id'     => $user->id,
                'workspace_id' => $workspace->id
            ]
        );
        $task         = $this->createTask(
            [
                'delegated_id'   => $user->id,
                'implementer_id' => $user->id,
                'owner_id'       => $user->id,
                'project_id'     => $project->id,
                'workspace_id'   => $workspace->id
            ]
        );
        $conversation = $this->createConversation(
            [
                'workspace_id' => $workspace->id,
                'owner_id'     => $user->id
            ]
        );
        $request      = [
            'text'            => 'Lorem Ipsum',
            'conversation_id' => $conversation->id,
            'is_visible'      => 1,
            'author_id'       => $user->id
        ];
        auth()->login($user);
        $response = $this->actingAs($user)->post('/api/messages', $request);
        $message  = Message::find($response->baseResponse->original['success']['message']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/messages/' . $message->id);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $task->delete();
        $conversation->delete();
        $message->delete();
    }

    public function testProjects()
    {
        echo('Testing projects api' . PHP_EOL);

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
        $request   = [
            'owner_id'     => $user->id,
            'status'       => 1,
            'title'        => 'Foo',
            'text'         => 'Bar',
            'workspace_id' => $workspace->id
        ];
        auth()->login($user);
        $response    = $this->actingAs($user)->post('/api/projects', $request);
        $new_project = Project::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get('/api/projects/' . $project->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/projects/' . $project->id, $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/projects/' . $project->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/projects/' . $project->id . '/restore/');
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $new_project->delete();
    }

    public function testTags()
    {
        echo('Testing tags api' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $request   = [
            'title'      => 'Lorem Ipsum',
            'is_visible' => 1
        ];
        auth()->login($user);
        $response = $this->actingAs($user)->post('/api/tags', $request);
        $tag      = Tag::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/tags/' . $tag->id);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $tag->delete();
    }

    public function testTasks()
    {
        echo('Testing tasks api' . PHP_EOL);

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
        $tag       = $this->createTag();
        $request   = [
            'delegated_id'   => $user->id,
            'implementer_id' => $user->id,
            'is_private'     => 0,
            'is_visible'     => 1,
            'owner_id'       => $user->id,
            'project_id'     => $project->id,
            'status'         => 1,
            'title'          => '',
            'text'           => 'Lorem ipsum',
            'time_mark'      => 'new',
            'workspace_id'   => $workspace->id,
            'sort'           => [0 => $task->id],
            'tag_id'         => $tag->id,
            'user_id'        => $user->id,
            'start'          => time(),
            'end'            => time()
        ];
        auth()->login($user);
        $response = $this->actingAs($user)->post('/api/tasks', $request);
        $response->assertStatus(200);
        $new_task = Task::find($response->baseResponse->original['success']['id']);
        $response = $this->actingAs($user)->get('/api/tasks/' . $task->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id, $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/tasks/' . $task->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/restore/');
        $response->assertStatus(200);
        $response  = $this->actingAs($user)->post('/api/tasks/createEmpty/', $request);
        $new_task2 = Task::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/tasks/saveOrder/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/addTag/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/removeTag/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/assign/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/changeTimeMark/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/togglePrivate/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/toggleSection/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/tasks/' . $task->id . '/toggleStatus/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/tasks/getCalendarEvents/', $request);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $project->delete();
        $task->delete();
        $tag->delete();
        $new_task->delete();
        $new_task2->delete();
    }

    public function testUsers()
    {
        echo('Testing users api' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $request   = [
            'name'     => 'Foo',
            'email'    => 'test' . str_random(10) . '@test.test',
            'password' => str_random(10),
            'role'     => 'user',
            'key'      => 'role',
            'value'    => 'admin'
        ];
        auth()->login($user);
        $response = $this->actingAs($user)->get('/api/users/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/', $request);
        $new_user = User::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/users/' . $user->id, $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/users/' . $user->id);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/changePassword/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/users/' . $user->id . '/changeProperty/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/checkEmailUnique/', $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/deleteAvatar/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/toggleConversationSubscribe/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/toggleEnableSound/');
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post('/api/users/toggleSubscribe/');
        $response->assertStatus(200);
        $request  = [
            'name'     => 'Foo',
            'email'    => 'test' . str_random(10) . '@test.test',
            'password' => str_random(10),
            'role'     => 'user',
            'key'      => 'role',
            'value'    => 'admin'
        ];
        $response = $this->actingAs($user)->put('/api/users/' . $user->id . '/updatePersonal/', $request);
        $response->assertStatus(200);
        Storage::fake('attachment');
        $response = $this->actingAs($user)->json(
            'POST',
            '/api/users/uploadAvatar/',
            [
                'attachment'  => UploadedFile::fake()->create('avatar.jpg', 100)
            ]
        );
        $file     = File::find($response->baseResponse->original['success']['id']);
        $response = $this->actingAs($user)->put('/api/users/' . $user->id . '/toggleWorkspaceAccess/', $request);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $new_user->delete();
        $file->delete();
    }

    public function testWorkspaces()
    {
        echo('Testing workspaces api' . PHP_EOL);

        $workspace = $this->createWorkspace();
        $user      = $this->createUser(
            [
                'workspace_id' => $workspace->id,
                'role'         => 'user'
            ]
        );
        $request   = [
            'title'       => 'Foo',
            'description' => 'Bar',
            'is_visible'  => 1
        ];
        auth()->login($user);
        $response      = $this->actingAs($user)->post('/api/workspaces', $request);
        $new_workspace = Workspace::find($response->baseResponse->original['success']['id']);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->put('/api/workspaces/' . $workspace->id, $request);
        $response->assertStatus(200);
        $response = $this->actingAs($user)->delete('/api/workspaces/' . $workspace->id);
        $response->assertStatus(200);
        $user->delete();
        $workspace->delete();
        $new_workspace->delete();
    }
}
