<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Workspace;

class WorkspaceController extends Controller
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
        $workspace = Workspace::create($request->all());

        if ($workspace->save()) {
            return ['success' => $workspace];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Workspace                $workspace
     *
     * @return \array
     */
    public function update(Workspace $workspace, Request $request)
    {
        $workspace->fill($request->all());

        if ($workspace->save()) {
            return ['success' => $workspace];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Workspace $workspace
     *
     * @return array
     */
    public function destroy(Workspace $workspace)
    {
        $workspace->is_visible = 0;

        if ($workspace->save()) {
            return ['success' => $workspace];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
