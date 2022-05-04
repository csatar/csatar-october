<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarReligions extends Migration
{
    public function up()
    {
        Schema::rename('csatar_csatar_religion', 'csatar_csatar_religions');
    }
    
    public function down()
    {
        Schema::rename('csatar_csatar_religions', 'csatar_csatar_religion');
    }
}
