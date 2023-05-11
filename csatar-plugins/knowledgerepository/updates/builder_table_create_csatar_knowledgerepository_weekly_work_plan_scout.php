<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryWeeklyWorkPlanScout extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_weekly_work_plan_scout', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('weekly_work_plan_id')->unsigned();
            $table->integer('scout_id')->unsigned();
            
            $table->foreign('weekly_work_plan_id', 'weekly_workplan_scout_foreign')->references('id')->on('csatar_knowledgerepository_weekly_work_plans');
            $table->foreign('scout_id', 'scout_weekly_workplan_foreign')->references('id')->on('csatar_csatar_scouts');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_weekly_work_plan_scout');
    }

}
