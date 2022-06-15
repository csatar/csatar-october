<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarCurrencies extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_currencies', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('code', 3);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_currencies');
    }
}
