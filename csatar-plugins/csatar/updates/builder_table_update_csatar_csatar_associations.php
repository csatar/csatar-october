<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAssociations extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->double('team_fee')->unsigned()->default(0);
            $table->integer('currency_id')->index('currency_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropColumn('team_fee');
            $table->dropIndex('currency_id');
            $table->dropColumn('currency_id');
        });
    }

}
