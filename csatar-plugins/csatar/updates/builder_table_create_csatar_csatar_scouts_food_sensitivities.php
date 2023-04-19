<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsFoodSensitivities extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_food_sensitivities', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('food_sensitivity_id')->unsigned();
            $table->string('comment', 255)->nullable();
            $table->primary(['scout_id','food_sensitivity_id'], 'csatar_csatar_scout_id_food_sensitivity_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_food_sensitivities');
    }
}
