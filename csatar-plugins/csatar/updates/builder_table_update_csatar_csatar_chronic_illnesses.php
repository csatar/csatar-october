<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarChronicIllnesses extends Migration
{
    public function up()
    {
        Schema::rename('csatar_csatar_chronic_illness', 'csatar_csatar_chronic_illnesses');
    }
    
    public function down()
    {
        Schema::rename('csatar_csatar_chronic_illnesses', 'csatar_csatar_chronic_illness');
    }
}
