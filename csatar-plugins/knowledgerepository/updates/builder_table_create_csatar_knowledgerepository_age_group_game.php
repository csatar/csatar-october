<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryAgeGroupGame extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_age_group_game', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('age_group_id')->nullable()->unsigned();
            $table->integer('game_id')->nullable()->unsigned();
            
            $table->foreign('age_group_id', 'game_age_group_id_foreign')->references('id')->on('csatar_csatar_age_groups');
            $table->foreign('game_id', 'age_group_game_id_foreign')->references('id')->on('csatar_knowledgerepository_games');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_age_group_game');
    }
}
