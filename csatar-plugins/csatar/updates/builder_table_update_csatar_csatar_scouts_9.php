<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts9 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->string('ecset_code', 14)->after('id')->unique()->nullable();
            $table->integer('team_id')->after('ecset_code')->index('team_id')->nullable()->unsigned();
            $table->integer('troop_id')->after('team_id')->index('troop_id')->nullable()->unsigned();
            $table->integer('patrol_id')->after('troop_id')->index('patrol_id')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('ecset_code');
            $table->dropColumn('team_id');
            $table->dropColumn('troop_id');
            $table->dropColumn('patrol_id');
        });
    }
}
