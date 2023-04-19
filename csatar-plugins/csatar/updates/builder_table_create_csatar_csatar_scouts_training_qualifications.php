<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsTrainingQualifications extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_training_qualifications', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('training_qualification_id')->unsigned();
            $table->date('date')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('qualification_certificate_number', 255)->nullable();
            $table->string('qualification', 255)->nullable();
            $table->string('qualification_leader', 255)->nullable();
            $table->primary(['scout_id','training_qualification_id'], 'csatar_csatar_scout_id_training_qualification_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_training_qualifications');
    }
}
