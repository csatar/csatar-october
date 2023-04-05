<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAssociations3 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->string('personal_identification_number_validator')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropColumn('personal_identification_number_validator');
        });
    }
}
