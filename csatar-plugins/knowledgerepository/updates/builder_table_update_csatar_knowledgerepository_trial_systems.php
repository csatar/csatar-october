<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositoryTrialSystems extends Migration
{

    public function up()
    {
        Schema::table('csatar_knowledgerepository_trial_systems', function($table)
        {
            $table->text('name')->change();
            $table->unique('id_string');
            $table->longText('effective_knowledge')->after('note')->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_knowledgerepository_trial_systems', function($table)
        {
            $table->string('name')->change();
            $table->dropIndex('csatar_knowledgerepository_trial_systems_id_string_unique');
            $table->dropColumn('effective_knowledge');
        });
    }

}
