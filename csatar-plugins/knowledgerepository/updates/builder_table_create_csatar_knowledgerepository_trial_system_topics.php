<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryTrialSystemTopics extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_trial_system_topics', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_trial_system_topics');
    }
}