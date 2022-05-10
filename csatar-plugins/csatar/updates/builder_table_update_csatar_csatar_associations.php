<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAssociations extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->renameColumn('description', 'leadership_presentation');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->renameColumn('leadership_presentation', 'description');
        });
    }
}
