<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function store(Request $request)
    {
        $project = Project::create($request->all());

        if ($project->save()) {
            return ['success' => $project];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * @param Project $project
     *
     * @return Project
     */
    public function show(Project $project)
    {
        return $project;
    }

    /**
     * @param Project $project
     * @param Request $request
     *
     * @return array
     */
    public function update(Project $project, Request $request)
    {
        $project->fill($request->all());

        if ($project->save()) {
            return ['success' => $project];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    /**
     * @param Project $project
     *
     * @return array
     */
    public function destroy(Project $project)
    {
        $project->status = 0;
        foreach ($project->tasks as $task) {
            $task->project_id = null;
            $task->save();
        }

        if ($project->save()) {
            return ['success' => $project];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }

    public function restore(Project $project)
    {
        $project->status = 1;

        if ($project->save()) {
            return ['success' => $project];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
