<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 11.10.2018
 * Time: 12:18
 */

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use \App\Classes\Socket\Pusher;
use App\Http\Controllers\MailController;
use App\Classes\Transformers\ModelTransformer;
use App\Models\Task;

class PusherService
{
    public static function pushConversationMentionData($request, $conversation){
        if (is_array($request->mentions)) {
            $mentions = $request->mentions;
            foreach ($mentions as $mention) {
                if ($refer = User::find($mention)) {
                    if (Auth::user()->id != $mention) {
                        $comment_text                = 'Пользователь ' . Auth::user()->name . ' упомянул вас в беседе "' . $conversation->title . '"';
                        $comment                     = Comment::sendPersonalComment($conversation->id, 'conversation_mention', $comment_text, $refer->id, 'conversation');
                        $comment->message_flag       = 'new_message_comment';
                        $comment->conversation_title = $conversation->title;
                        Pusher::sendDataToServer(
                            [
                                'topic_id' => 'onNewData',
                                'data'     => ModelTransformer::transformComment($comment)
                            ]
                        );
                        if ($refer->conversations_subscribe == 1) {
                            MailController::conversationMentionMessage($refer->email, $conversation, $refer->id);
                        }
                        User::commentNotification($refer->id);
                    }
                }
            }
        }
    }

    public static function pushCommentMentionData($request)
    {
        $current_user = User::find(Auth::user()->id);
        $task         = Task::find($request->task_id);
        $task_title   = $task->title ? $task->title : 'Без названия';
        $mentions = explode(",", $request->mentions);
        foreach ($mentions as $mention) {
            if ($refer = User::find($mention)) {
                if ($current_user->id != $mention) {
                    $mention_comment_text          = 'Пользователь ' . $current_user->link . ' упомянул вас в комментарии к задаче "' . $task_title . '"';
                    $mention_comment               = Comment::sendPersonalComment($task->id, 'comment_mention', $mention_comment_text, $refer->id, 'task');
                    $mention_comment->message_flag = 'new_task_comment';
                    Pusher::sendDataToServer(
                        [
                            'topic_id' => 'onNewData',
                            'data'     => ModelTransformer::transformComment($mention_comment)
                        ]
                    );
                    if ($refer->subscribe == 1) {
                        MailController::commentMentionMessage($refer->email, $task, $refer, $current_user);
                    }
                    User::commentNotification($refer->id);
                }
            }
        }
    }

    public static function pushMessageMentionData($request, $message)
    {
        if (is_array($request->mentions)) {
            $mentions = $request->mentions;
            foreach ($mentions as $mention) {
                if ($refer = User::find($mention)) {
                    if (Auth::user()->id != $mention) {
                        $comment_text                = 'Пользователь ' . User::find($message->author_id)->name . ' упомянул вас в беседе "' . $message->conversation->title . '"';
                        $comment                     = Comment::sendPersonalComment($message->conversation->id, 'conversation_mention', $comment_text, $refer->id, 'conversation');
                        $comment->message_flag       = 'new_message_comment';
                        $comment->conversation_title = $message->conversation->title;
                        Pusher::sendDataToServer(
                            [
                                'topic_id' => 'onNewData',
                                'data'     => ModelTransformer::transformComment($comment)
                            ]
                        );
                        if ($refer->conversations_subscribe == 1) {
                            MailController::conversationMentionMessage($refer->email, $message->conversation, $refer->id);
                        }
                        User::commentNotification($refer->id);
                    }
                }
            }
        }
    }

    public static function pushTaskMentionData($request, $task)
    {
        $task_title = $task->title ? $task->title : 'Без названия';
        $current_user           = User::find(Auth::user()->id);
        $mentions = explode(",", $request->mentions);
        foreach ($mentions as $mention) {
            if ($refer = User::find($mention)) {
                if ($current_user->id != $mention) {
                    $mention_comment_text = 'Пользователь ' . $current_user->link . ' упомянул вас в задаче "' . $task_title . '"';
                    $mention_comment               = Comment::sendPersonalComment($task->id, 'task_mention', $mention_comment_text, $refer->id, 'task');
                    $mention_comment->message_flag = 'new_task_comment';
                    Pusher::sendDataToServer([
                        'topic_id' => 'onNewData',
                        'data'     => ModelTransformer::transformComment($mention_comment)
                    ]);
                    if ($refer->subscribe == 1) {
                        MailController::taskMentionMessage($refer->email, $task, $refer, $current_user);
                    }
                    User::commentNotification($refer->id);
                }
            }
        }
    }
}