<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarMandates extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_mandates', function($table)
        {
            $table->date('start_date')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->date('end_date')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_mandates', function($table)
        {
            $table->dateTime('start_date')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->dateTime('end_date')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
