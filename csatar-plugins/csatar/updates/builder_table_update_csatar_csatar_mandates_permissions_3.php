<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarMandatesPermissions3 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_mandates_permissions', function($table)
        {
            $table->timestamp('created_at')->after('delete');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_mandates_permissions', function($table)
        {
            $table->dropColumn('created_at');
        });
    }
}
