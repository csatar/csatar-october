<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameLocation extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_location', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_id')->unsigned();
            $table->integer('location_id')->unsigned();
            
            $table->foreign('game_id', 'location_game_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
            $table->foreign('location_id', 'game_location_id_foreign')->references('id')->on('csatar_knowledgerepository_locations');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_location');
    }
}
