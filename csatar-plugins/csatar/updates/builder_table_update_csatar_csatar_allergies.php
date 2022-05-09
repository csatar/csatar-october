<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAllergies extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_allergies', function($table)
        {
            $table->string('title', 255)->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_allergies', function($table)
        {
            $table->string('title', 10)->change();
        });
    }
}
