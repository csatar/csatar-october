<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts3 extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->boolean('google_two_fa_is_activated')->after('google_two_fa_secret_key')->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('google_two_fa_is_activated');
        });
    }

}
