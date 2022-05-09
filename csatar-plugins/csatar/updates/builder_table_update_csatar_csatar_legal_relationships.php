<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarLegalRelationships extends Migration
{
    public function up()
    {
        Schema::rename('csatar_csatar_legal_relationship', 'csatar_csatar_legal_relationships');
    }
    
    public function down()
    {
        Schema::rename('csatar_csatar_legal_relationships', 'csatar_csatar_legal_relationship');
    }
}
