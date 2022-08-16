<?php

namespace App\Http\Controllers\Api;

use App\Classes\Transformers\ModelTransformer;
use App\Models\File;
use App\Models\User;
use App\Models\Follower;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\PusherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ConversationController extends Controller
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
        $conversation = Conversation::create($request->all());
        $conversation->save();
        Follower::followersToConversation($conversation);
        $message = Message::createMessage($request->message_text, $request->owner_id, $request->attachment_id, $conversation->id);
        PusherService::pushConversationMentionData($request, $conversation);

        $result['conversation'] = ModelTransformer::transformConversation($conversation);
        $result['message']      = ModelTransformer::transformMessage($message);
        $result['users']        = User::where('is_active', 1)->get();
        $result['current_user'] = User::find(Auth::user()->id);
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
     * @param  Conversation $conversation
     *
     * @return array
     */
    public function destroy(Conversation $conversation)
    {
        $conversation->is_visible = 0;
        $conversation->task_id    = null;
        $conversation->save();
        $conversation = ModelTransformer::transformConversation($conversation);

        if ($conversation) {
            return ['success' => $conversation];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
