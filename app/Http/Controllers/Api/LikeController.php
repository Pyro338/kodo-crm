<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        if (Like::isLiked(Auth::user()->id, $request->type, $request->post_id)) {
            $result['liked'] = Like::detachLike($request);
        } else {
            $result['liked'] = Like::attachLike($request);
        }

        if ($result) {
            return ['success' => $result];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
