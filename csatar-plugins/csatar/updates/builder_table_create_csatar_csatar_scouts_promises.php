<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScoutsPromises extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_promises', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('promise_id')->unsigned();
            $table->date('date')->nullable();
            $table->string('location', 255)->nullable();
            $table->primary(['scout_id','promise_id'], 'csatar_csatar_scout_id_promise_id_primary');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_promises');
    }
}
