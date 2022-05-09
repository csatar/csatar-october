<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts5 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('special_diet')->unsigned();
            $table->integer('religion')->unsigned();
            $table->integer('tshirt_size')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('special_diet');
            $table->dropColumn('religion');
            $table->dropColumn('tshirt_size');
        });
    }
}
