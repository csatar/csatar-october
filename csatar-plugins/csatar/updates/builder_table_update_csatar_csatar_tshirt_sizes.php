<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarTshirtSizes extends Migration
{
    public function up()
    {
        Schema::rename('csatar_csatar_tshirt_size', 'csatar_csatar_tshirt_sizes');
    }
    
    public function down()
    {
        Schema::rename('csatar_csatar_tshirt_sizes', 'csatar_csatar_tshirt_size');
    }
}
