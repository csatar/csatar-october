<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositorySharingSettings extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_sharing_settings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('association_id')->nullable()->unsigned();
            $table->integer('association2_id')->nullable()->unsigned();
            
            $table->foreign('association_id', 'sharing_settings_association_id')->references('id')->on('csatar_csatar_associations');
            $table->foreign('association2_id', 'sharing_settings_association2_id')->references('id')->on('csatar_csatar_associations');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_sharing_settings');
    }
}