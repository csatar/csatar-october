<?php

namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryOvamtvWorkPlanMaterials extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_ovamtv_work_plan_material', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('ovamtv_work_plan_id')->unsigned();
            $table->integer('new_material_id')->nullable()->unsigned();
            $table->integer('old_material_id')->nullable()->unsigned();
            
            $table->foreign('ovamtv_work_plan_id', 'ovamtv_work_plan_foreign')->references('id')->on('csatar_knowledgerepository_ovamtv_work_plans');
            $table->foreign('new_material_id', 'new_material_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
            $table->foreign('old_material_id', 'old_material_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_ovamtv_work_plan_material');
    }
}