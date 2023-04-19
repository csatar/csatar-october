<?php
namespace Csatar\Forms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarFormsForms extends Migration
{
    public function up()
    {
        Schema::table('csatar_forms_forms', function($table)
        {
            $table->string('slug')->after('title');
        });
    }

    public function down()
    {
        Schema::table('csatar_forms_forms', function($table)
        {
            $table->dropColumn('slug');
        });
    }
}
