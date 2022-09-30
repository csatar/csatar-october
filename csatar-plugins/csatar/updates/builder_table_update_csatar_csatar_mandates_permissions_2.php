<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarMandatesPermissions2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_mandates_permissions', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_mandates_permissions', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}
