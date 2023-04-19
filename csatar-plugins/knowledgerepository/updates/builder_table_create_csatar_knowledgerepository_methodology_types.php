<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryMethodologyTypes extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_methodology_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->integer('sort_order')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_methodology_types');
    }
}
