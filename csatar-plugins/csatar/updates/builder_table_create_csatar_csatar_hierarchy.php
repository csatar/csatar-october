<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarHierarchy extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_hierarchy', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->integer('parent_id')->nullable();
            $table->smallInteger('sort_order')->unsigned()->default(1);
            $table->smallInteger('nest_left')->nullable();
            $table->smallInteger('nest_right')->nullable();
            $table->smallInteger('nest_depth')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_hierarchy');
    }
}
