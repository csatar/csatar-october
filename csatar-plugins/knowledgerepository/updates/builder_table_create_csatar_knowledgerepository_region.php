<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryRegion extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_region', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 100)->nullable();
            $table->integer('big_parent_id')->nullable();
            $table->integer('mid_parent_id')->nullable();
            $table->integer('small_parent_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_region');
    }
}
