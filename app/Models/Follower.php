<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 24.05.2018
 * Time: 14:41
 */

namespace App\Models;


use App\Http\Controllers\MailController;
use \App\Classes\Socket\Pusher;
use Illuminate\Support\Facades\Auth;
use App\Classes\Transformers\ModelTransformer;


class Follower extends Model
{
    protected $fillable = [
        'type',
        'follower_id',
        'post_id'
    ];

    public static function getFollowers($post_id, $type)
    {
        $users = [];
        $followers = Follower::where('type', $type)->where('post_id', $post_id)->get();
        foreach ($followers as $key=>$follower){
            $users[$key] = User::find($follower->follower_id);
        }

        return $users;
    }

    public static function isFollower($user_id, $type, $post_id)
    {
        if (Follower::where('follower_id', $user_id)
            ->where('type', $type)
            ->where('post_id', $post_id)
            ->first()) {
            return true;
        } else {
            return false;
        }
    }

    public static function getFollower($user_id, $type, $post_id){
        return Follower::where('follower_id', $user_id)
            ->where('type', $type)
            ->where('post_id', $post_id)
            ->first();
    }

    public static function createFollower($params)
    {
        $new_follower              = new Follower;
        $new_follower->follower_id = $params['user_id'];
        $new_follower->type        = $params['type'];
        $new_follower->post_id     = $params['post_id'];
        $new_follower->save();

        return $new_follower;
    }

    public static function toggle($request)
    {
        if (Follower::isFollower($request->user_id, $request->type, $request->post_id)) {
            $follower = Follower::getFollower($request->user_id, $request->type, $request->post_id);
            $follower->delete();
            $result = 'unfollowed';
        } else {
            Follower::createFollower(
                [
                    'type'    => $request->type,
                    'post_id' => $request->post_id,
                    'user_id' => $request->user_id
                ]
            );
            $result = 'followed';
        }

        return $result;
    }

    public static function followersToConversation($conversation)
    {
        if ($conversation->task_id) {
            $users = User::all();
            foreach ($users as $user) {
                if (Follower::isFollower($user->id, 'task', $conversation->task_id)) {
                    Follower::createFollower(
                        [
                            'user_id' => $user->id,
                            'type'    => 'conversation',
                            'post_id' => $conversation->id
                        ]
                    );
                }
            }
        } else {
            Follower::createFollower(
                [
                    'user_id' => User::find(Auth::user()->id)->id,
                    'type'    => 'conversation',
                    'post_id' => $conversation->id
                ]
            );
        }
    }

    public static function newComment($follower, $request, $task)
    {
        if ($follower->id != Auth::user()->id) {
            $comment_data                 = $request->all();
            $comment_data['recipient_id'] = $follower->id;
            $comment_data['is_arhive']    = 0;
            $comment                      = Comment::create($comment_data);
            if ($follower->subscribe == 1) {
                MailController::newCommentMessage($follower->email, $task, $follower, $comment);
            }
            $comment->message_flag = 'new_task_comment';
            Pusher::sendDataToServer(
                [
                    'topic_id' => 'onNewData',
                    'data'     => ModelTransformer::transformComment($comment)
                ]
            );
            User::commentNotification($follower->id);
        }
    }

    public static function newMessage($follower, $message)
    {
        if (Auth::user()->id != $follower->id) {
            if ($message->conversation->title) {
                $conversation_title = $message->conversation->title;
            } else {
                $conversation_title = 'Без заголовка';
            }
            $comment_text                = 'Новое сообщение в беседе "' . $conversation_title . '" за которой вы следите: ' . '<p><i>' . $message->text . '</i></p>';
            $comment                     = Comment::sendPersonalComment($message->conversation_id, 'new_message', $comment_text, $follower->id, 'conversation');
            $comment->message_flag       = 'new_message_comment';
            $comment->conversation_title = $conversation_title;
            $comment->workspace_id       = $message->conversation->workspace_id;
            if ($follower->conversations_subscribe == 1) {
                MailController::messageCreatedMessage($follower->email, $message->conversation, $follower->id);
            }
            Pusher::sendDataToServer(
                [
                    'topic_id' => 'onNewData',
                    'data'     => ModelTransformer::transformComment($comment)
                ]
            );
            User::commentNotification($follower->id);
        }
    }

    public static function taskStatusChanged($follower, $comment_text, $task)
    {
        $current_user = User::find(Auth::user()->id);
        if ($follower->id != $current_user->id) {
            $comment               = Comment::sendPersonalComment($task->id, 'task_status_changed', $comment_text, $follower->id, 'task');
            $comment->message_flag = 'new_task_comment';
            if ($follower->subscribe == 1) {
                switch ($task->status) {
                    case 0:
                    case 1:
                    case 2:
                        MailController::taskRestoredMessage($follower->email, $task, $follower, $current_user);
                        break;
                    case 3:
                        MailController::taskFinishedMessage($follower->email, $task, $follower, $current_user);
                        break;
                }
            }
            Pusher::sendDataToServer(
                [
                    'topic_id' => 'onNewData',
                    'data'     => ModelTransformer::transformComment($comment)
                ]
            );
            User::commentNotification($follower->id);
        }
    }

    public static function taskDelegated($follower, $comment_text, $task)
    {
        if ($follower->id != Auth::user()->id) {
            $comment               = Comment::sendPersonalComment($task->id, 'task_delegated', $comment_text, $follower->id, 'task');
            $comment->message_flag = 'new_task_comment';
            if ($follower->subscribe == 1) {
                MailController::taskDelegatedMessage($follower->email, $task, $follower);
            }
            Pusher::sendDataToServer(
                [
                    'topic_id' => 'onNewData',
                    'data'     => ModelTransformer::transformComment($comment)
                ]
            );
            User::commentNotification($follower->id);
        }
    }

    public static function taskRestored($follower, $comment_text, $task)
    {
        $current_user = User::find(Auth::user()->id);
        if ($follower->id != $current_user->id) {
            $comment               = Comment::sendPersonalComment($task->id, 'task_restored', $comment_text, $task->id, 'task');
            $comment->message_flag = 'new_task_comment';
            if ($follower->subscribe == 1) {
                MailController::taskUpdatedMessage($follower->email, $task, $follower, $current_user);
            }
            Pusher::sendDataToServer(
                [
                    'topic_id' => 'onNewData',
                    'data'     => ModelTransformer::transformComment($comment)
                ]
            );
            User::commentNotification($follower->id);
        }
    }

    public static function taskDeleted($follower, $comment_text, $task)
    {
        if ($follower->id != Auth::user()->id) {
            $comment               = Comment::sendPersonalComment($task->id, 'task_deleted', $comment_text, $follower->id, 'task');
            $comment->message_flag = 'new_task_comment';
            if ($follower->subscribe == 1) {
                MailController::taskDeletedMessage($follower->email, $task, $follower->id);
            }
            Pusher::sendDataToServer(
                [
                    'topic_id' => 'onNewData',
                    'data'     => ModelTransformer::transformComment($comment)
                ]
            );
            User::commentNotification($follower->id);
        }
    }

    public static function taskUpdated($follower, $task)
    {
        $current_user = User::find(Auth::user()->id);
        $comment_text = 'Задача была изменена';
        $comment_type = 'task_updated';
        if ($follower->id != $current_user->id) {
            $comment               = new Comment;
            $comment->task_id      = $task->id;
            $comment->type         = 'task_delegated';
            $comment->recipient_id = $follower->id;
            if ($task->is_visible == 0) {
                $comment_text  = 'Задача "' . $task->title . '" создана пользователем ' . $current_user->name
                    . ' для пользователя ' . User::find($task->delegated_id)->name;
                $comment_type  = 'task_created';
                $comment->type = $comment_type;
                $comment->text = $comment_text;

                if ($follower->subscribe == 1) {
                    MailController::taskCreatedMessage($follower->email, $task, $follower);
                }
            } else {
                $comment->type = $comment_type;
                $comment->text = $comment_text;

                if ($follower->subscribe == 1) {
                    MailController::taskUpdatedMessage($follower->email, $task, $follower, $current_user);
                }
            }
            $comment->is_arhive = 0;
            $comment->save();
            $comment->message_flag = 'new_task_comment';
            Pusher::sendDataToServer([
                'topic_id' => 'onNewData',
                'data'     => ModelTransformer::transformComment($comment)
            ]);
            User::commentNotification($follower->id);
        }
    }
}