<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryWeeklyWorkPlanSpareGame extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_weekly_work_plan_spare_game', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('weekly_work_plan_id')->unsigned();
            $table->integer('game_id')->unsigned();
            
            $table->foreign('weekly_work_plan_id', 'weekly_workplan_game_foreign')->references('id')->on('csatar_knowledgerepository_weekly_work_plans');
            $table->foreign('game_id', 'game_weekly_workplan_foreign')->references('id')->on('csatar_knowledgerepository_games');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_weekly_work_plan_spare_game');
    }

}
