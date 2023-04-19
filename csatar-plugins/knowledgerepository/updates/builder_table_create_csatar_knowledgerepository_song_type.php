<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositorySongType extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_song_type', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_song_type');
    }
}
