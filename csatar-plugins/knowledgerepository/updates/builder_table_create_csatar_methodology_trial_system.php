<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarMethodologyTrialSystem extends Migration
{

    public function up()
    {
        Schema::create('csatar_methodology_trial_system', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('methodology_id')->unsigned();
            $table->integer('trial_system_id')->unsigned();

            $table->foreign('methodology_id', 'trial_system_methodology_id_foreign')->references('id')->on('csatar_knowledgerepository_methodologies');
            $table->foreign('trial_system_id', 'methodology_trial_system_id_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_methodology_trial_system');
    }

}
