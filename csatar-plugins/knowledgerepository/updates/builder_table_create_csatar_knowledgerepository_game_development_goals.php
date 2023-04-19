<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameDevelopmentGoals extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_development_goals', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->string('note')->nullable();
            $table->integer('sort_order')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_development_goals');
    }

}
