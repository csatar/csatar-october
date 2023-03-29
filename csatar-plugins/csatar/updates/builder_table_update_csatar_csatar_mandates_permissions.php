<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarMandatesPermissions extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_mandates_permissions', function($table)
        {
            $table->increments('id')->first()->unsigned();
            $table->timestamp('created_at')->nullable();
;
            $table->dropColumn('2fa');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_mandates_permissions', function($table)
        {
            $table->boolean('2fa')->nullable();
            $table->dropColumn('id');
            $table->dropColumn('created_at');

        });
    }
}