<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarTrainings extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_trainings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->text('comment')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_trainings');
    }
}