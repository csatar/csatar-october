<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarTeamReports extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_team_reports', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('team_id')->index('team_id')->unsigned();
            $table->integer('year')->unsigned();
            $table->integer('number_of_adult_patrols')->unsigned()->default(0);
            $table->integer('number_of_explorer_patrols')->unsigned()->default(0);
            $table->integer('number_of_scout_patrols')->unsigned()->default(0);
            $table->integer('number_of_cub_scout_patrols')->unsigned()->default(0);
            $table->integer('number_of_mixed_patrols')->unsigned()->default(0);
            $table->text('scouting_year_report_team_camp');
            $table->text('scouting_year_report_homesteading');
            $table->text('scouting_year_report_programs');
            $table->text('scouting_year_team_applications');
            $table->string('spiritual_leader_name', 255);
            $table->integer('spiritual_leader_religion_id')->index('spiritual_leader_religion_id')->unsigned();
            $table->string('spiritual_leader_occupation', 255);
            $table->double('team_fee')->unsigned();
            $table->double('total_amount')->unsigned();
            $table->integer('currency_id')->index('currency_id')->unsigned();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_team_reports');
    }
}
