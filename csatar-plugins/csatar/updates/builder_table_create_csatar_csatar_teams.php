<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarTeams extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_teams', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->string('team_number', 10);
            $table->string('address', 255);
            $table->date('foundation_date');
            $table->string('phone', 255);
            $table->string('email', 255);
            $table->string('website', 255)->nullable();
            $table->string('facebook_page', 255)->nullable();
            $table->string('contact_name', 255);
            $table->string('contact_email', 255);
            $table->text('history')->nullable();
            $table->text('coordinates')->nullable();
            $table->text('leadership_presentation');
            $table->text('description');
            $table->string('juridical_person_name', 255);
            $table->string('juridical_person_address', 255);
            $table->string('juridical_person_tax_number', 255);
            $table->string('juridical_person_bank_account', 255);
            $table->string('home_supplier_name', 255)->nullable();
            $table->integer('district_id')->index('district_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_teams');
    }
}
