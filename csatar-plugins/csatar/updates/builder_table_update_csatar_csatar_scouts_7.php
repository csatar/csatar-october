<?php namespace Csatar\Csatar\Updates;

use Db;
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts7 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dateTime('inactivated_at')->nullable();
        });

        Db::select('UPDATE csatar_csatar_scouts SET inactivated_at = updated_at WHERE is_active = 0');

        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('is_active');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->boolean('is_active')->default(0)->nullable();
        });

        Db::select('UPDATE csatar_csatar_scouts SET is_active = 1 WHERE inactivated_at IS NULL');

        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('inactivated_at');
        });
    }
}
