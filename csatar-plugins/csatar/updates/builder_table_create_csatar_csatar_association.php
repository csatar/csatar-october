<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarAssociation extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_associations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->text('coordinates')->nullable();
            $table->string('contact_name', 255);
            $table->string('contact_email', 255);
            $table->string('address', 255);
            $table->string('bank_account', 255)->nullable();
            $table->text('leadership_presentation');
            $table->string('ecset_code_suffix', 2)->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_associations');
    }
}
