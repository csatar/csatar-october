<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts7 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('legal_relationship_id')->nullable()->change();
            $table->integer('special_diet_id')->nullable()->change();
            $table->integer('religion_id')->nullable()->change();
            $table->integer('tshirt_size_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->integer('legal_relationship_id')->nullable(false)->change();
            $table->integer('special_diet_id')->nullable(false)->change();
            $table->integer('religion_id')->nullable(false)->change();
            $table->integer('tshirt_size_id')->nullable(false)->change();
        });
    }
}
