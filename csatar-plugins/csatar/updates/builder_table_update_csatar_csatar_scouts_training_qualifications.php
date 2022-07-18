<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScoutsTrainingQualifications extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts_training_qualifications', function($table)
        {
            $table->integer('training_id')->unsigned();
            $table->dropColumn('qualification');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts_training_qualifications', function($table)
        {
            $table->dropColumn('training_id');
            $table->string('qualification', 255)->nullable();
        });
    }
}
