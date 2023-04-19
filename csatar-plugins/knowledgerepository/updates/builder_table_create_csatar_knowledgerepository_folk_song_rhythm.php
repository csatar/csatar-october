<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryFolkSongRhythm extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_folk_song_rhythm', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_folk_song_rhythm');
    }
}
