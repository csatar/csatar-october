<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAssociations2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->string('name_abbreviation')->after('name')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropColumn('name_abbreviation');
        });
    }
}
