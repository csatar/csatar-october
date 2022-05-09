<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScoutsAllergies extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts_allergies', function($table)
        {
            $table->string('details', 255)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts_allergies', function($table)
        {
            $table->string('details', 255)->nullable(false)->change();
        });
    }
}
