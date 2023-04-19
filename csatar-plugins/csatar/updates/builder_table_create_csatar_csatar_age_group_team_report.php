<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarAgeGroupTeamReport extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_age_group_team_report', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('age_group_id')->unsigned();
            $table->integer('team_report_id')->unsigned();
            $table->integer('number_of_patrols_in_age_group')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_age_group_team_report');
    }
}
