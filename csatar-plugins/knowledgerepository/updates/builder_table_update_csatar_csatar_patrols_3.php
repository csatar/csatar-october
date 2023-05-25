<?php
namespace Csatar\KnowledgeRepository\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableUpdateCsatarCsatarPatrols3 extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            // check if column exists
            if (!Schema::hasColumn('csatar_csatar_patrols', 'trial_system_trial_type_id')) {
                $table->integer('trial_system_trial_type_id')->unsigned()->nullable();
            }
            // check if foreign key exists
            if (Schema::hasColumn('csatar_csatar_patrols', 'trial_system_trial_type_id')
                && !Schema::hasColumn('csatar_csatar_patrols', 'patrol_trial_system_trial_type_foreign')
            ) {
                $table->foreign('trial_system_trial_type_id', 'patrol_trial_system_trial_type_foreign')->references('id')->on('csatar_knowledgerepository_trial_system_trial_types');
            }
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
