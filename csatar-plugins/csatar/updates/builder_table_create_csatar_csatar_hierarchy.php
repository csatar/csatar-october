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
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_hierarchy');
    }
}
