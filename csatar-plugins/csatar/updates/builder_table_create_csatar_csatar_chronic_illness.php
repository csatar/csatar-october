<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarChronicIllness extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_chronic_illness', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('title', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_chronic_illness');
    }
}
