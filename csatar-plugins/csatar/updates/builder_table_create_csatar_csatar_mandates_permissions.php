<?php namespace Csatar\Csatar\Updates;

use DB;
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarMandatesPermissions extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_mandates_permissions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('mandate_type_id')->unsigned();
            $table->string('model');
            $table->string('field')->nullable();
            $table->boolean('own')->nullable();
            $table->boolean('2fa')->nullable();
            $table->boolean('obligatory')->nullable();
            $table->boolean('create')->nullable();
            $table->boolean('read')->nullable();
            $table->boolean('update')->nullable();
            $table->boolean('delete')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->foreign('mandate_type_id')->references('id')->on('csatar_csatar_mandates');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_mandates_permissions');
    }
}
