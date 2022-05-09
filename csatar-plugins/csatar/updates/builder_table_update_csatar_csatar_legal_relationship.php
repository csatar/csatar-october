<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarLegalRelationship extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_legal_relationship', function($table)
        {
            $table->renameColumn('name', 'title');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_legal_relationship', function($table)
        {
            $table->renameColumn('title', 'name');
        });
    }
}
