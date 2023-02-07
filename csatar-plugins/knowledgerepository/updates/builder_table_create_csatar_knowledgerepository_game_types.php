<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGameTypes extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_game_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->string('note')->nullable();
            $table->integer('sort_order')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_game_types');
    }
}
