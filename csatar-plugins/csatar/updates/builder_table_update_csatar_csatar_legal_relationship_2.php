<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarLegalRelationship2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_legal_relationship', function($table)
        {
            $table->smallInteger('sort_order')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_legal_relationship', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
}
