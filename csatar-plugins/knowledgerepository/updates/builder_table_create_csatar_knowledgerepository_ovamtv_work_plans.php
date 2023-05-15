<?php

namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryOvamtvWorkPlans extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_ovamtv_work_plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('patrol_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('creator_csatar_code')->nullable();
            $table->string('patrol_name_gender')->nullable();
            $table->string('patrol_leader')->nullable();
            $table->string('deputy_patrol_leaders')->nullable();
            $table->text('patrol_members')->nullable();
            $table->string('troop')->nullable();
            $table->string('age_group_test')->nullable();
            $table->date('start_date')->nullable();
            $table->string('notes', 500)->nullable();
            $table->text('goals')->nullable();
            $table->text('tasks')->nullable();
            
            $table->foreign('team_id')->references('id')->on('csatar_csatar_teams');
            $table->foreign('patrol_id')->references('id')->on('csatar_csatar_patrols');
            $table->foreign('creator_csatar_code', 'creator_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_ovamtv_work_plans');
    }

}
