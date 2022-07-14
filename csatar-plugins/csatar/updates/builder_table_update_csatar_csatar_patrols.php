<?php namespace Csatar\Csatar\Updates;

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
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->dropColumn('age_group_id');
            $table->string('age_group', 255);
        });
    }
}
