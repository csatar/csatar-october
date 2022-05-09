<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts6 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('legal_relationship_id')->unsigned();
            $table->integer('special_diet_id')->unsigned();
            $table->integer('religion_id')->unsigned();
            $table->integer('tshirt_size_id')->unsigned();
            $table->dropColumn('legal_relationship');
            $table->dropColumn('special_diet');
            $table->dropColumn('religion');
            $table->dropColumn('tshirt_size');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('legal_relationship_id');
            $table->dropColumn('special_diet_id');
            $table->dropColumn('religion_id');
            $table->dropColumn('tshirt_size_id');
            $table->integer('legal_relationship')->unsigned();
            $table->integer('special_diet')->unsigned();
            $table->integer('religion')->unsigned();
            $table->integer('tshirt_size')->unsigned();
        });
    }
}
