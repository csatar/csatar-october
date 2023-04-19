<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarMandateTypes extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_mandate_types', function($table)
        {
            $table->boolean('is_hidden_frontend')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_mandate_types', function($table)
        {
            $table->dropColumn('is_hidden_frontend');
        });
    }
}
