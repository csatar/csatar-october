<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScouts extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('user_id')
                ->unsigned()
                ->foreign('user_id', 'user_foreign')
                ->references('id')
                ->on('users');
            $table->smallInteger('gender')->unsigned();
            $table->integer('personal_identification_number')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts');
    }
}
