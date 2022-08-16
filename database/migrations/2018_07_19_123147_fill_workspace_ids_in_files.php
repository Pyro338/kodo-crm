<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\File;
use App\Models\Task;

class FillWorkspaceIdsInFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $files = File::all();
        foreach ($files as $file){
            if($task = Task::find($file->task_id)){
                if($task->workspace_id){
                    $file->workspace_id = $task->workspace_id;
                    $file->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
