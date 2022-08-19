<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarTroops extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->dropColumn('troop_leader_name');
            $table->dropColumn('troop_leader_phone');
            $table->dropColumn('troop_leader_email');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->string('troop_leader_name', 255);
            $table->string('troop_leader_phone', 255);
            $table->string('troop_leader_email', 255);
        });
    }
}
