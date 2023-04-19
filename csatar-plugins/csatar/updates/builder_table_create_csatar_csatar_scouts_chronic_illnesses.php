<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1015 extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_chronic_illnesses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('chronic_illness_id')->unsigned();
            $table->primary(['scout_id','chronic_illness_id'], 'csatar_csatar_scout_id_chronic_illness_id_primary');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_chronic_illnesses');
    }
}
