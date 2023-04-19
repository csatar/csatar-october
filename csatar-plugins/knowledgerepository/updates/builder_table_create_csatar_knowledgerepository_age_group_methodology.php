<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryAgeGroupMethodology extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_age_group_methodology', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('age_group_id')->unsigned();
            $table->integer('methodology_id')->unsigned();

            $table->foreign('age_group_id', 'methodology_age_group_id_foreign')->references('id')->on('csatar_csatar_age_groups');
            $table->foreign('methodology_id', 'age_group_methodology_id_foreign')->references('id')->on('csatar_knowledgerepository_methodologies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_age_group_methodology');
    }
}
