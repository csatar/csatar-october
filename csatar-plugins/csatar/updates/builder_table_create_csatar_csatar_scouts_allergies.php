<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1017 extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts_allergies', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('scout_id')->unsigned();
            $table->integer('allergy_id')->unsigned();
            $table->string('comment', 255)->nullable();
            $table->primary(['scout_id','allergy_id'], 'csatar_csatar_scout_id_allergy_id_primary');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts_allergies');
    }
}
