<?php

namespace App\Http\Controllers\Api;

use App\Services\PusherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Classes\Transformers\ModelTransformer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HtmlHelper;
use \App\Classes\Socket\Pusher;
use App\Models\Follower;
use App\Models\File;

class MessageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function store(Request $request)
    {
        $current_user       = User::find(Auth::user()->id);
        $message            = Message::create($request->all());
        $message->author_id = $current_user->id;
        $message->save();
        $message->message_flag = 'new_message';
        $message->text         = HtmlHelper::findLinks($message->text);

        $conversation             = $message->conversation;
        $conversation->updated_at = time();
        $conversation->save();
        Pusher::sendDataToServer(
            [
                'topic_id' => 'onNewData',
                'data'     => ModelTransformer::transformMessage($message)
            ]
        );

        PusherService::pushMessageMentionData($request, $message);
        $followers = Follower::getFollowers($message->conversation_id, 'conversation');
        foreach ($followers as $follower) {
            Follower::newMessage($follower, $message);
        }

        $result['message'] = ModelTransformer::transformMessage($message);
        if ($request->attachment_id) {
            $result['attachment'] = File::find($request->attachment_id);
        }

        if ($result) {
            return ['success' => $result];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Message $message
     *
     * @return array
     */
    public function destroy(Message $message)
    {
        $message->text          = '<span style="color: #ccc">Сообщение было удалено</span>';
        $message->attachment_id = null;
        $message->save();
        $message = ModelTransformer::transformMessage($message);

        if ($message) {
            return ['success' => $message];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function attach(Request $request)
    {
        $new_file = File::createFile($request, $request->attachment);

        if ($new_file->save()) {
            return ['success' => $new_file];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
