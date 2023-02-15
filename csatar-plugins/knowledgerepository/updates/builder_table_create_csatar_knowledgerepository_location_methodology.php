<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryLocationMethodology extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_location_methodology', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('location_id')->unsigned();
            $table->integer('methodology_id')->unsigned();

            $table->foreign('location_id', 'methodology_location_id_foreign')->references('id')->on('csatar_knowledgerepository_locations');
            $table->foreign('methodology_id', 'location_methodology_id_foreign')->references('id')->on('csatar_knowledgerepository_methodologies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_location_methodology');
    }
}
