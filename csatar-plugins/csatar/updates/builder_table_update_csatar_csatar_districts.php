<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarDistricts extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->integer('association_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->dropColumn('association_id');
        });
    }
}
