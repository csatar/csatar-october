<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryActivityTypes extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_activity_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('model')->nullable();
            $table->text('categories')->nullable();
            $table->text('tooltip')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_activity_types');
    }

}
