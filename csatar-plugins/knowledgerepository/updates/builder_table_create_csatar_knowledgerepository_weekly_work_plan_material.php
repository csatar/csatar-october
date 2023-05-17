<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryWeeklyWorkPlanMaterial extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_weekly_work_plan_material', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('weekly_work_plan_id')->unsigned();
            $table->integer('new_material_id')->nullable()->unsigned();
            $table->integer('old_material_id')->nullable()->unsigned();

            $table->foreign('weekly_work_plan_id', 'weekly_work_plan_foreign')->references('id')->on('csatar_knowledgerepository_weekly_work_plans');
            $table->foreign('new_material_id', 'weekly_work_plan_new_material_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
            $table->foreign('old_material_id', 'weekly_work_plan_old_material_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_weekly_work_plan_material');
    }

}
