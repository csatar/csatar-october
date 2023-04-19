<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarTeamReports extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_team_reports', function($table)
        {
            $table->text('extra_fields')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_team_reports', function($table)
        {
            $table->dropColumn('extra_fields');
        });
    }
}
