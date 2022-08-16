<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Classes\Transformers\ModelTransformer;

class TagController extends Controller
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
        if ($tag = Tag::where('title', $request->tag)->first()) {
            if ($tag->is_visible == 0) {
                $tag->is_visible = 1;
                $tag->save();
                $tag->is_new = 'restored';
            } else {
                $tag->is_new = 'old';
            }
        } else {
            $tag        = new Tag;
            $tag->title = $request->tag;
            $tag->save();
            $tag->is_new = 'new';
        }
        $tag = ModelTransformer::transformTag($tag);

        if ($tag) {
            return ['success' => $tag];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Tag $tag
     *
     * @return array
     */
    public function destroy(Tag $tag)
    {
        $tag->is_visible = 0;
        $tag->save();
        $tag = ModelTransformer::transformTag($tag);

        if ($tag) {
            return ['success' => $tag];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
