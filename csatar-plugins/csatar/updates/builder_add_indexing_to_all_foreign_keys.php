<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderAddIndexingToAllForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table) {
            $table->index('legal_relationship_id');
            $table->index('special_diet_id');
            $table->index('religion_id');
            $table->index('tshirt_size_id');
        });
        Schema::table('csatar_csatar_districts', function($table) {
            $table->index('association_id');
        });
        Schema::table('csatar_csatar_teams', function($table) {
            $table->index('district_id');
        });
        Schema::table('csatar_csatar_troops', function($table) {
            $table->index('team_id');
        });
        Schema::table('csatar_csatar_patrols', function($table) {
            $table->integer('team_id')->unsigned()->change();
            $table->integer('troop_id')->unsigned()->change();
            $table->index('team_id');
            $table->index('troop_id');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table) {
            $table->dropIndex('csatar_csatar_scouts_legal_relationship_id_index');
            $table->dropIndex('csatar_csatar_scouts_special_diet_id_index');
            $table->dropIndex('csatar_csatar_scouts_religion_id_index');
            $table->dropIndex('csatar_csatar_scouts_tshirt_size_id_index');
        });
        Schema::table('csatar_csatar_districts', function($table) {
            $table->dropIndex('csatar_csatar_districts_association_id_index');
        });
        Schema::table('csatar_csatar_teams', function($table) {
            $table->dropIndex('csatar_csatar_teams_district_id_index');
        });
        Schema::table('csatar_csatar_troops', function($table) {
            $table->dropIndex('csatar_csatar_troops_team_id_index');
        });
        Schema::table('csatar_csatar_patrols', function($table) {
            $table->integer('team_id')->unsigned(false)->change();
            $table->integer('troop_id')->unsigned(false)->change();
            $table->dropIndex('csatar_csatar_patrols_team_id_index');
            $table->dropIndex('csatar_csatar_patrols_troop_id_index');
        });
    }
}