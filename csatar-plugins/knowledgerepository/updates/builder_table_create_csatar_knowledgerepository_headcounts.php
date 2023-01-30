<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryHeadcounts extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_headcounts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('description')->nullable();
            $table->string('note')->nullable();
            $table->integer('min')->nullable()->unsigned();
            $table->integer('max')->nullable()->unsigned();
            $table->integer('order')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_headcounts');
    }
}
