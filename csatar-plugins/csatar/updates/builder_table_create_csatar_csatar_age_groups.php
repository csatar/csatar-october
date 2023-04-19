<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarAgeGroups extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_age_groups', function($table)
        {

            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('note')->nullable();
            $table->integer('sort_order')->unsigned()->default(0);
            $table->integer('association_id')->unsigned();
            $table->timestamp('deleted_at')->nullable();
            
            $table->foreign('association_id')->references('id')->on('csatar_csatar_associations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_age_groups');
    }
}
