<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameTrialSystem extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_trial_system', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_id')->unsigned();
            $table->integer('trial_system_id')->unsigned();

            $table->foreign('game_id', 'trial_system_game_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
            $table->foreign('trial_system_id', 'game_trial_system_id_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_trial_system');
    }

}
