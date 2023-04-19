<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryWorkPlans extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_work_plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('year');
            $table->text('troops')->nullable();
            $table->text('patrols')->nullable();
            $table->text('frame_story')->nullable();
            $table->text('team_goals')->nullable();
            $table->text('team_notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->foreign('team_id')->references('id')->on('csatar_csatar_teams');
            $table->unique(['team_id', 'year'], 'team_year');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_work_plans');
    }
}
