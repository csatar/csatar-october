<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts8 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dateTime('birthdate')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->date('birthdate')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}