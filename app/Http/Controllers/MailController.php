<?php

namespace App\Http\Controllers;

use App\Mail\CalendarRemind;
use App\Mail\CommentMention;
use App\Mail\Welcome;
use App\Mail\TaskCreated;
use App\Mail\TaskUpdated;
use App\Mail\TaskFinished;
use App\Mail\TaskRestored;
use App\Mail\TaskDelegated;
use App\Mail\TaskDeleted;
use App\Mail\NewComment;
use App\Mail\TaskMention;
use App\Mail\ConversationMention;
use App\Mail\MessageCreated;
use App\Mail\Invite;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class MailController extends Controller
{
    public static function calendarRemindMessage()
    {
        $users = User::where('is_active', 1)->get();
        foreach ($users as $user) {
            $tasks = Task::where('is_visible', 1)
                ->where('status', 1)
                ->where('delegated_id', $user->id)
                ->where('due_date', '<=', date('Y-m-d'))
                ->get();
            if ($tasks->count() > 0) {
                $params = [
                    'tasks_count' => $tasks->count(),
                    'recipient'   => $user
                ];
                Mail::to($user->email)->send(new CalendarRemind($params));
            }
        }
    }

    public static function commentMentionMessage($email, $task, $recipient, $initiator)
    {
        if (!$task->title) {
            $task->title = 'Без названия';
        }
        $params = [
            'task'      => $task,
            'recipient' => $recipient,
            'initiator' => $initiator
        ];
        Mail::to($email)->send(new CommentMention($params));
    }

    public static function conversationMentionMessage($email, $conversation, $recipient_id)
    {
        $params = [
            'conversation' => $conversation,
            'recipient_id' => $recipient_id
        ];
        Mail::to($email)->send(new ConversationMention($params));
    }

    public static function inviteMessage($email, $user, $password, $message)
    {
        $params = [
            'user'     => $user,
            'password' => $password,
            'message'  => $message
        ];
        Mail::to($email)->send(new Invite($params));
    }

    public static function messageCreatedMessage($email, $conversation, $recipient_id)
    {
        $params = [
            'conversation' => $conversation,
            'recipient_id' => $recipient_id
        ];
        Mail::to($email)->send(new MessageCreated($params));
    }

    public static function newCommentMessage($email, $task, $recipient, $comment)
    {
        $params = [
            'task'         => $task,
            'recipient' => $recipient,
            'comment'      => $comment
        ];
        Mail::to($email)->send(new NewComment($params));
    }

    public static function taskCreatedMessage($email, $task, $recipient)
    {
        $params = [
            'task'         => $task,
            'recipient' => $recipient
        ];
        Mail::to($email)->send(new TaskCreated($params));
    }

    public static function taskDelegatedMessage($email, $task, $recipient)
    {
        $params = [
            'task'           => $task,
            'recipient'   => $recipient,
            'delegated_name' => User::find($task->delegated_id)->name
        ];
        Mail::to($email)->send(new TaskDelegated($params));
    }

    public static function taskDeletedMessage($email, $task, $recipient_id)
    {
        $params = [
            'task'         => $task,
            'recipient_id' => $recipient_id
        ];
        Mail::to($email)->send(new TaskDeleted($params));
    }

    public static function taskFinishedMessage($email, $task, $recipient, $initiator)
    {
        if (!$task->title) {
            $task->title = 'Без названия';
        }
        $params = [
            'task'      => $task,
            'recipient' => $recipient,
            'initiator' => $initiator
        ];
        Mail::to($email)->send(new taskFinished($params));
    }

    public static function taskMentionMessage($email, $task, $recipient, $initiator)
    {
        if (!$task->title) {
            $task->title = 'Без названия';
        }
        $params = [
            'task'      => $task,
            'recipient' => $recipient,
            'initiator' => $initiator
        ];
        Mail::to($email)->send(new TaskMention($params));
    }

    public static function taskRestoredMessage($email, $task, $recipient, $initiator)
    {
        if (!$task->title) {
            $task->title = 'Без названия';
        }
        $params = [
            'task'      => $task,
            'recipient' => $recipient,
            'initiator' => $initiator
        ];
        Mail::to($email)->send(new taskRestored($params));
    }

    public static function taskUpdatedMessage($email, $task, $recipient, $initiator)
    {
        if (!$task->title) {
            $task->title = 'Без названия';
        }
        $params = [
            'task'      => $task,
            'recipient' => $recipient,
            'initiator' => $initiator
        ];
        Mail::to($email)->send(new TaskUpdated($params));
    }

    public static function welcomeMessage($email)
    {
        Mail::to($email)->send(new Welcome());
    }
}