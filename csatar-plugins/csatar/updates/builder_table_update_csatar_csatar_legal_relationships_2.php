<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarLegalRelationships2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_legal_relationships', function($table)
        {
            $table->smallInteger('sort_order')->unsigned()->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_legal_relationships', function($table)
        {
            $table->smallInteger('sort_order')->unsigned(false)->default(null)->change();
        });
    }
}
