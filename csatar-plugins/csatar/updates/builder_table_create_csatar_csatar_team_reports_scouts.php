<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarTeamReportsScouts extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_team_reports_scouts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('team_report_id')->unsigned();
            $table->integer('scout_id')->unsigned();
            $table->string('name', 255);
            $table->integer('legal_relationship_id')->index('legal_relationship_id')->unsigned();
            $table->integer('leadership_qualification_id')->index('leadership_qualification_id')->unsigned();
            $table->double('membership_fee')->unsigned();
            $table->primary(['team_report_id','scout_id'], 'csatar_csatar_team_report_id_scout_id_primary');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_team_reports_scouts');
    }
}