<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarAssociationsLegalRelationships extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_associations_legal_relationships', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('association_id')->unsigned();
            $table->integer('legal_relationship_id')->unsigned();
            $table->double('membership_fee')->nullable()->unsigned()->default(0);
            $table->primary(['association_id','legal_relationship_id'], 'csatar_csatar_association_id_legal_relationship_id_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_associations_legal_relationships');
    }
}
