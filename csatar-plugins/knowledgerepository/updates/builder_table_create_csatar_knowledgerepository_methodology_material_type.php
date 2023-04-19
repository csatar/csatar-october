<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryMethodologyMaterialType extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_methodology_material_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_methodology_material_types');
    }
}
