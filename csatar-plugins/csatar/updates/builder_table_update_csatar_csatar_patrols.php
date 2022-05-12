<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarPatrols extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('legal_relationship_id')->index('legal_relationship_id')->change();
            $table->integer('special_diet_id')->index('special_diet_id')->change();
            $table->integer('religion_id')->index('religion_id')->change();
            $table->integer('tshirt_size_id')->index('tshirt_size_id')->change();
        });
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->integer('association_id')->index('association_id')->change();
        });
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->integer('district_id')->index('district_id')->change();
        });
        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->integer('team_id')->index('team_id')->change();
        });
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->integer('team_id')->index('team_id')->unsigned()->change();
            $table->integer('troop_id')->index('troop_id')->unsigned()->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropIndex('legal_relationship_id');
            $table->dropIndex('special_diet_id');
            $table->dropIndex('religion_id');
            $table->dropIndex('tshirt_size_id');
        });
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->dropIndex('association_id');
        });
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->dropIndex('district_id');
        });
        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->dropIndex('team_id');
        });
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->integer('team_id')->unsigned(false)->change();
            $table->integer('troop_id')->unsigned(false)->change();
            $table->dropIndex('team_id');
            $table->dropIndex('troop_id');
        });
    }
}
