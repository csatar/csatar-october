<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAssociations6 extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->integer('special_workplan_age_group_id')->nullable()->unsigned();
            $table->foreign('special_workplan_age_group_id', 'special_workplan_age_group_foreign')->references('id')->on('csatar_csatar_age_groups');
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropForeign('special_workplan_age_group_foreign');
            $table->dropColumn('special_workplan_age_group_id');
        });
    }

}
