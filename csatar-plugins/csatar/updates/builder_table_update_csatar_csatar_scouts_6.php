<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts6 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('citizenship_country_id')->after('gender')->unsigned()->nullable();
            $table->foreign('citizenship_country_id')->references('id')->on('rainlab_location_countries');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('citizenship_country_id');
        });
    }
}
