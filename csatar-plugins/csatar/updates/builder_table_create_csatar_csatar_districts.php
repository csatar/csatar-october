<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarDistricts extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_districts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->string('address', 255);
            $table->string('phone', 255);
            $table->string('email', 255);
            $table->string('website', 255)->nullable();
            $table->string('facebook_page', 255)->nullable();
            $table->text('coordinates')->nullable();
            $table->string('contact_name', 255);
            $table->string('contact_email', 255);
            $table->text('leadership_presentation');
            $table->text('description');
            $table->string('bank_account', 255)->nullable();
            $table->integer('association_id')->index('association_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_districts');
    }
}
