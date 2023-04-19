<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryHeadcountMethodology extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_headcount_methodology', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('headcount_id')->unsigned();
            $table->integer('methodology_id')->unsigned();

            $table->foreign('headcount_id', 'methodology_headcount_id_foreign')->references('id')->on('csatar_knowledgerepository_headcounts');
            $table->foreign('methodology_id', 'headcount_methodology_id_foreign')->references('id')->on('csatar_knowledgerepository_methodologies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_headcount_methodology');
    }

}
