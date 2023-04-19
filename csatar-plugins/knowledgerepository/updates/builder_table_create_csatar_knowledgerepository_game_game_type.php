<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameGameType extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_game_type', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('game_id')->unsigned();
            $table->integer('game_type_id')->unsigned();
            
            $table->foreign('game_id', 'game_type_game_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
            $table->foreign('game_type_id', 'game_game_type_id_foreign')->references('id')->on('csatar_knowledgerepository_game_types');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_game_type');
    }
}
