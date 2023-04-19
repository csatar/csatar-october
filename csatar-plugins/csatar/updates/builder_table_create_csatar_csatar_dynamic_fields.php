<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarDynamicFields extends Migration
{

    public function up()
    {
        Schema::create('csatar_csatar_dynamic_fields', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('association_id')->index('association_id')->unsigned();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('model', 255);
            $table->integer('extra_fields_max_id')->unsigned()->default(0);
            $table->text('extra_fields_definition')->nullable();

            $table->foreign('association_id')->references('id')->on('csatar_csatar_associations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_dynamic_fields');
    }

}
