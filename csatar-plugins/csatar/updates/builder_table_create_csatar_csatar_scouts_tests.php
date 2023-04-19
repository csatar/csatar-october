<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsTests extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_tests', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('test_id')->unsigned();
            $table->date('date')->nullable();
            $table->string('location', 255)->nullable();
            $table->primary(['scout_id','test_id'], 'csatar_csatar_scout_id_test_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_tests');
    }
}
