<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryAgeGroupSong extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_age_group_song', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('age_group_id')->nullable()->unsigned();
            $table->integer('song_id')->nullable()->unsigned();
            
            $table->foreign('age_group_id', 'song_age_group_id_foreign')->references('id')->on('csatar_csatar_age_groups');
            $table->foreign('song_id', 'age_group_song_id_foreign')->references('id')->on('csatar_knowledgerepository_songs');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_age_group_song');
    }
}
