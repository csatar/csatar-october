<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarTeams extends Migration
{
    public function up()
    {
        Schema::rename('csatar_csatar_team', 'csatar_csatar_teams');
    }
    
    public function down()
    {
        Schema::rename('csatar_csatar_teams', 'csatar_csatar_team');
    }
}
