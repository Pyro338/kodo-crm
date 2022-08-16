<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkspaceIdToConversations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->integer('workspace_id')->nullable();
        });
        $conversations = \App\Models\Conversation::all();
        foreach ($conversations as $conversation){
            $user = \App\Models\User::find($conversation->owner_id);
            $conversation->workspace_id = $user->workspace_id;
            $conversation->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('workspace_id');
        });
    }
}
