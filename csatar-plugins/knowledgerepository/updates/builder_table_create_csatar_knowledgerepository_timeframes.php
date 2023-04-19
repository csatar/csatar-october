<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryTimeframes extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_durations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->integer('min')->nullable()->unsigned();
            $table->integer('max')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_durations');
    }

}
