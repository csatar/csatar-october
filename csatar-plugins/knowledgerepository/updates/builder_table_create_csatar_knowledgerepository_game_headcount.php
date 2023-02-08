<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameHeadcount extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_headcount', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_id')->unsigned();
            $table->integer('headcount_id')->unsigned();

            $table->foreign('game_id', 'headcount_game_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
            $table->foreign('headcount_id', 'game_headcount_id_foreign')->references('id')->on('csatar_knowledgerepository_headcounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_headcount');
    }
}
