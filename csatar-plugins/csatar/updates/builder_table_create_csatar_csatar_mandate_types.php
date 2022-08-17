<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarMandateTypes extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_mandate_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->integer('association_id')->index('association_id')->unsigned();
            $table->string('organization_type_model_name', 255);
            $table->boolean('required')->default(false);
            $table->boolean('overlap_allowed')->default(false);
            $table->integer('parent_id')->nullable()->unsigned();
            $table->smallInteger('nest_left')->nullable()->unsigned();
            $table->smallInteger('nest_right')->nullable()->unsigned();
            $table->smallInteger('nest_depth')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_mandate_types');
    }
}
