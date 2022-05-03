<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->string('family_name')->after('user_id');
            $table->string('given_name')->after('family_name');
            $table->string('email')->after('given_name');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('family_name');
            $table->dropColumn('given_name');
            $table->dropColumn('email');
        });
    }
}