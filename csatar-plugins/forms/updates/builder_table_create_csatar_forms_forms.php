<?php namespace Csatar\Forms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarFormsForms extends Migration
{
    public function up()
    {
        Schema::create('csatar_forms_forms', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('model');
            $table->string('fields_config')->default('fields.yaml');
            $table->string('title');
            $table->text('description')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_forms_forms');
    }
}
