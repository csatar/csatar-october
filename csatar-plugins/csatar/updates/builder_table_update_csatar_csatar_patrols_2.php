<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarPatrols2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->dropColumn('patrol_leader_name');
            $table->dropColumn('patrol_leader_phone');
            $table->dropColumn('patrol_leader_email');
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->string('patrol_leader_name', 255);
            $table->string('patrol_leader_phone', 255);
            $table->string('patrol_leader_email', 255);
        });
    }
}
