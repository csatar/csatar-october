<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositorySongTrialSystem extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_song_trial_system', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('song_id')->nullable()->unsigned();
            $table->integer('trial_system_id')->nullable()->unsigned();
            
            $table->foreign('song_id', 'trial_system_song_id_foreign')->references('id')->on('csatar_knowledgerepository_songs');
            $table->foreign('trial_system_id', 'song_trial_system_id_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_song_trial_system');
    }
}
