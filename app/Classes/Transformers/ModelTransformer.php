<?php
/**
 * Created by PhpStorm.
 * User: Pyro338
 * Date: 05.09.2018
 * Time: 17:11
 */

namespace App\Classes\Transformers;

use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Message;
use Themsaid\Transformers\AbstractTransformer;

class ModelTransformer extends AbstractTransformer
{
    public static function transformComment(Comment $item)
    {
        $output = [
            'id'                 => $item->id,
            'task_id'            => $item->task_id,
            'type'               => $item->type,
            'text'               => $item->text,
            'created_at'         => $item->created_at,
            'updated_at'         => $item->updated_at,
            'author_id'          => $item->author_id,
            'author'             => $item->author,
            'recipient_id'       => $item->recipient_id,
            'is_arhive'          => $item->is_arhive,
            'is_visible'         => $item->is_visible,
            'conversation_id'    => $item->conversation_id,
            'unique_id'          => $item->unique_id,
            'conversation_title' => $item->conversation_title,
            'task_name'          => $item->task_name,
            'task_status'        => $item->task_status,
        ];

        if (is_object($item->task)) {
            $output['task'] = [
                'id'           => $item->task->id,
                'project_id'   => $item->task->project_id,
                'workspace_id' => $item->task->workspace_id,
            ];
            if (is_object($item->task->project)) {
                $output['task']['project'] = [
                    'color' => $item->task->project->color,
                    'title' => $item->task->project->title,
                    'alias' => $item->task->project->alias
                ];
            }
        }

        return $output;
    }

    public static function transformConversation($item)
    {
        $output = [
            'owner_id'     => $item->owner_id,
            'id'           => $item->id,
            'workspace_id' => $item->workspace_id,
            'date'         => $item->date,
            'title'        => $item->title,
            'text_preview' => $item->text_preview,
            'is_liked'     => $item->is_liked,
            'likes_count'  => $item->likes_count,
            'user'         => [
                'color'            => $item->user->color,
                'background_image' => $item->user->background_image,
                'fullname'         => $item->user->fullname,
                'department'       => $item->user->department,
                'office'           => $item->user->office,
                'first_letters'    => $item->user->first_letters,
            ]
        ];

        return $output;
    }

    public static function transformMessage(Message $item)
    {
        $output = [
            'message_flag'    => $item->message_flag,
            'author_id'       => $item->author_id,
            'id'              => $item->id,
            'text'            => $item->text,
            'conversation_id' => $item->conversation_id,
            'author'          => $item->author,
            'time'            => $item->time,
            'attachment_id'   => $item->attachment_id,
            'is_liked'        => $item->is_liked,
            'likes_count'     => $item->likes_count,
            'conversation'    => [
                'id'           => $item->conversation->id,
                'date'         => $item->conversation->date,
                'text-preview' => $item->conversation->text_preview,
                'user'         => [
                    'color'            => $item->conversation->user->color,
                    'background_image' => $item->conversation->user->background_image,
                    'fullname'         => $item->conversation->user->fullname,
                    'department'       => $item->conversation->user->department,
                    'office'           => $item->conversation->user->office,
                    'first_letters'    => $item->conversation->user->first_letters,
                ]
            ],
            'user'            => [
                'color'            => $item->user->color,
                'background_image' => $item->user->background_image,
                'fullname'         => $item->user->fullname,
                'department'       => $item->user->department,
                'office'           => $item->user->office,
                'first_letters'    => $item->user->first_letters,
            ]
        ];

        return $output;
    }

    public static function transformTag($item)
    {
        $output = [
            'color'       => $item->color,
            'id'          => $item->id,
            'is_liked'    => $item->is_liked,
            'is_new'      => $item->is_new,
            'is_visible'  => $item->is_visible,
            'likes_count' => $item->likes_count,
            'title'       => $item->title,
        ];

        return $output;
    }

    public static function transformTask($item)
    {
        $output = [
            'delegated_id' => $item->delegated_id,
            'due_date'     => $item->due_date,
            'id'           => $item->id,
            'is_private'   => $item->is_private,
            'project_id'   => $item->project_id,
            'status'       => $item->status,
            'tags'         => [],
            'text'         => $item->text,
            'title'        => $item->title,
            'workspace_id' => $item->workspace_id,
            'user'         => [
                'color'            => $item->user->color,
                'background_image' => $item->user->background_image,
                'fullname'         => $item->user->fullname,
                'department'       => $item->user->department,
                'office'           => $item->user->office,
                'first_letters'    => $item->user->first_letters,
            ]
        ];

        if (is_object($item->conversation)) {
            $output['conversation'] = [
                'id' => $item->conversation->id,
            ];
        }

        if (is_object($item->parent_task)) {
            $output['parent_task'] = [
                'id'    => $item->parent_task->id,
                'title' => $item->parent_task->title,
            ];
        }

        if (is_object($item->project)) {
            $output['project'] = [
                'id'           => $item->project->id,
                'title'        => $item->project->title,
                'color'        => $item->project->color,
                'alias'        => $item->project->alias,
                'workspace_id' => $item->project->workspace_id
            ];
        }

        if (is_object($item->subtasks)) {
            foreach ($item->subtasks as $key => $subtask) {
                $output['subtasks'][$key]['id']     = $subtask->id;
                $output['subtasks'][$key]['status'] = $subtask->status;
                $output['subtasks'][$key]['title']  = $subtask->title;
            }
        }

        if (is_object($item->tags)) {
            foreach ($item->tags as $key => $tag) {
                $output['tags'][$key]['color'] = $tag->color;
                $output['tags'][$key]['id']    = $tag->id;
                $output['tags'][$key]['title'] = $tag->title;
            }
        }

        return $output;
    }
}