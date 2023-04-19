<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarTeams extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->smallInteger('status')->nullable()->default(1);
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->dropColumn('status');
        });
    }
}
