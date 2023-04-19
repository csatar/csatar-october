<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->string('google_two_fa_secret_key', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('google_two_fa_secret_key');
        });
    }
}
