<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarProfessionalQualifications extends Migration
{

    public function up()
    {
        Schema::create('csatar_csatar_professional_qualifications', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->text('comment')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_professional_qualifications');
    }

}
