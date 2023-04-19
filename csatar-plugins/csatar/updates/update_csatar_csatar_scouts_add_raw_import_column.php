<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateCsatarCsatarScoutsAddRawImportColumn extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->text('raw_import')->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('raw_import');
        });
    }
}
