<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryMethodologyTool extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_methodology_tool', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('methodology_id')->unsigned();
            $table->integer('tool_id')->unsigned();

            $table->foreign('methodology_id', 'tool_methodology_id_foreign')->references('id')->on('csatar_knowledgerepository_methodologies');
            $table->foreign('tool_id', 'methodology_tool_id_foreign')->references('id')->on('csatar_knowledgerepository_tools');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_methodology_tool');
    }
}
