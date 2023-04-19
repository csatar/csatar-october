<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsSpecialTests extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_special_tests', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('special_test_id')->unsigned();
            $table->date('date')->nullable();
            $table->string('location', 255)->nullable();
            $table->primary(['scout_id','special_test_id'], 'csatar_csatar_scout_id_special_test_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_special_tests');
    }
}
