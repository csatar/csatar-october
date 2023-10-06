<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryAgeGroupMatchings extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_age_group_matchings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('age_group1_id')->unsigned();
            $table->integer('age_group2_id')->unsigned();
            
            $table->foreign('age_group1_id', 'matching_age_group1_id')->references('id')->on('csatar_csatar_age_groups');
            $table->foreign('age_group2_id', 'matching_age_group2_id')->references('id')->on('csatar_csatar_age_groups');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_age_group_matchings');
    }
}