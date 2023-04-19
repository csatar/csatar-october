<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAssociations4 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->date('team_report_submit_start_date')->nullable();
            $table->date('team_report_submit_end_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropColumn('team_report_submit_start_date');
            $table->dropColumn('team_report_submit_end_date');
        });
    }
}
