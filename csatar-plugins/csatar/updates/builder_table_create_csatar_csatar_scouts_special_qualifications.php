<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsSpecialQualifications extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_special_qualifications', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('special_qualification_id')->unsigned();
            $table->date('date')->nullable();
            $table->string('location', 255)->nullable();
            $table->primary(['scout_id','special_qualification_id'], 'csatar_csatar_scout_id_special_qualification_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_special_qualifications');
    }
}
