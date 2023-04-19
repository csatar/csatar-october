<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsProfessionalQualifications extends Migration
{

    public function up()
    {
        Schema::create('csatar_csatar_scouts_professional_qualifications', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('professional_qualification_id')->unsigned();
            $table->date('date')->nullable();
            $table->string('location', 255)->nullable();
            $table->primary(['scout_id','professional_qualification_id'], 'csatar_csatar_scout_id_professional_qualification_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_professional_qualifications');
    }

}
