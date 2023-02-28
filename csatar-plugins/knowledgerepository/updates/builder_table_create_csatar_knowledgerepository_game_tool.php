<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameTool extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_tool', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_id')->unsigned();
            $table->integer('tool_id')->unsigned();

            $table->foreign('game_id', 'tool_game_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
            $table->foreign('tool_id', 'game_tool_id_foreign')->references('id')->on('csatar_knowledgerepository_tools');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_tool');
    }
}
