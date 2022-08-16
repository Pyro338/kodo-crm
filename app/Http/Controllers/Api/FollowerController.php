<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\User;

class FollowerController extends Controller
{
    public function toggle(Request $request)
    {
        $result['followed'] = Follower::toggle($request);
        $result['user']     = User::find($request->user_id);

        if ($result) {
            return ['success' => $result];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
