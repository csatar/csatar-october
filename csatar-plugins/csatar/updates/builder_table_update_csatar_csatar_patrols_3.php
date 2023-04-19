<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarPatrols3 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->integer('trial_system_trial_type_id')->unsigned()->nullable();
            $table->foreign('trial_system_trial_type_id', 'patrol_trial_system_trial_type_foreign')->references('id')->on('csatar_knowledgerepository_trial_systems');
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->dropForeign('patrol_trial_system_trial_type_foreign');
            $table->dropColumn('trial_system_trial_type_id');
        });
    }
}
