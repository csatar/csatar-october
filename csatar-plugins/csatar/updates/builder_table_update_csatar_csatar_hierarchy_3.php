<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarHierarchy3 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_hierarchy', function($table)
        {
            $table->smallInteger('nest_left')->nullable();
            $table->smallInteger('nest_right')->nullable();
            $table->smallInteger('nest_depth')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_hierarchy', function($table)
        {
            $table->dropColumn('nest_left');
            $table->dropColumn('nest_right');
            $table->dropColumn('nest_depth');
        });
    }
}
