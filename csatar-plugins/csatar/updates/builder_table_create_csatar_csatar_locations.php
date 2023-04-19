<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarLocations extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_locations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->string('country', 255)->nullable();
            $table->string('county', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('street_type', 255)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('number', 255)->nullable();
            $table->string('code', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_locations');
    }
}
