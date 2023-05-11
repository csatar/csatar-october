<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryWeeklyWorkPlans extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_weekly_work_plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('patrol_id')->unsigned();
            $table->integer('ovamtv_work_plan_id')->nullable()->unsigned();
            $table->integer('new_material_id')->nullable()->unsigned();
            $table->integer('old_material_id')->nullable()->unsigned();
            $table->string('patrol_name')->nullable();
            $table->string('patrol_leader')->nullable();
            $table->string('deputy_patrol_leaders')->nullable();
            $table->dateTime('start_date_time')->nullable();
            $table->text('scouts_list')->nullable();
            $table->text('advertisement')->nullable();
            $table->text('extra_tools')->nullable();
            $table->text('evaluation')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('creator_csatar_code')->nullable();
            $table->string('updater_csatar_code')->nullable();

            $table->foreign('patrol_id', 'patrol_weekly_foreign')->references('id')->on('csatar_csatar_patrols');
            $table->foreign('new_material_id', 'new_material_weekly_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
            $table->foreign('old_material_id', 'old_material_weekly_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
            $table->foreign('creator_csatar_code', 'creator_code_weekly_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
            $table->foreign('updater_csatar_code', 'updater_code_weekly_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
            $table->foreign('ovamtv_work_plan_id', 'ovamtv_work_plan_weekly_foreign')->references('id')->on('csatar_knowledgerepository_ovamtv_work_plans');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_weekly_work_plans');
    }

}
