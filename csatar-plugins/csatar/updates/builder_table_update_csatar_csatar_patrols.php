<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarPatrols extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->integer('age_group_id')->unsigned()->default(0);
            $table->dropColumn('age_group');

            $table->foreign('age_group_id')->references('id')->on('csatar_csatar_age_groups');
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->dropForeign('csatar_csatar_patrols_age_group_id_foreign');

            $table->dropColumn('age_group_id');
            $table->string('age_group', 255);
        });
    }

}
