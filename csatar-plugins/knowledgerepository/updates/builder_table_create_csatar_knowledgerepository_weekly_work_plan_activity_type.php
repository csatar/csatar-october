<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryWeeklyWorkPlanActivityType extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_weekly_work_plan_activity_type', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('weekly_work_plan_id')->unsigned();
            $table->integer('activity_type_id')->unsigned()->nullable();
            $table->string('programmable_type')->nullable();
            $table->integer('programmable_id')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->unsigned()->nullable();
            $table->smallInteger('duration')->unsigned()->nullable();

            $table->foreign('weekly_work_plan_id', 'weekly_workplan_foreign')->references('id')->on('csatar_knowledgerepository_weekly_work_plans');
            $table->foreign('activity_type_id', 'activity_type_foreign')->references('id')->on('csatar_knowledgerepository_activity_types');
        });

    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_weekly_work_plan_activity_type');
    }

}
