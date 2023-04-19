<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dateTime('accepted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('accepted_at');
        });
    }
}
