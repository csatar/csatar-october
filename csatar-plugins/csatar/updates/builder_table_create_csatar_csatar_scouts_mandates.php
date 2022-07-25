<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsMandates extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_mandates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('scout_id')->unsigned();
            $table->integer('mandate_id')->unsigned();
            $table->integer('mandate_model_id')->unsigned();
            $table->integer('mandate_model_type')->unsigned();
            $table->integer('mandate_model_name')->unsigned();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->text('comment')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_mandates');
    }
}