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
            $table->integer('scout_id')->unsigned();
            $table->integer('mandate_id')->unsigned();
            $table->integer('mandate_model_id')->default(0)->unsigned();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->primary(['scout_id','mandate_id','mandate_model_id'], 'csatar_csatar_scout_id_mandate_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_mandates');
    }
}