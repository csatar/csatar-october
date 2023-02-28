<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameDuration extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_duration', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_id')->unsigned();
            $table->integer('duration_id')->unsigned();

            $table->foreign('game_id', 'game_duration_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
            $table->foreign('duration_id', 'duration_game_id_foreign')->references('id')->on('csatar_knowledgerepository_durations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_duration');
    }
}
