<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('user_id')->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->string('personal_identification_number', 20)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('user_id')->nullable(false)->change();
            $table->string('email', 255)->nullable(false)->change();
            $table->integer('personal_identification_number')->nullable(false)->unsigned()->default(null)->comment(null)->change();
        });
    }
}
