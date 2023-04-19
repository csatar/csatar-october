<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameDevelopmentGoalGame extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_development_goal_game', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_development_goal_id')->unsigned();
            $table->integer('game_id')->unsigned();

            $table->foreign('game_development_goal_id', 'game_game_development_goal_id_foreign')->references('id')->on('csatar_knowledgerepository_game_development_goals');
            $table->foreign('game_id', 'game_development_goal_game_id_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_development_goal_game');
    }
}
