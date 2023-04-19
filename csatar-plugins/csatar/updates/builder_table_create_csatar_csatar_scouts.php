<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarScouts extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_scouts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('ecset_code', 14)->unique()->nullable();
            $table->integer('team_id')->index('team_id')->nullable()->unsigned();
            $table->integer('troop_id')->index('troop_id')->nullable()->unsigned();
            $table->integer('patrol_id')->index('patrol_id')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('family_name');
            $table->string('given_name');
            $table->string('email', 255)->nullable();
            $table->smallInteger('gender')->unsigned();
            $table->string('personal_identification_number', 20);
            $table->boolean('is_active')->default(1)->nullable();
            $table->integer('legal_relationship_id')->index('legal_relationship_id')->nullable()->unsigned();
            $table->integer('special_diet_id')->index('special_diet_id')->nullable()->unsigned();
            $table->integer('religion_id')->index('religion_id')->nullable()->unsigned();
            $table->integer('tshirt_size_id')->index('tshirt_size_id')->nullable()->unsigned();
            $table->string('name_prefix', 255)->nullable();
            $table->string('nickname', 255)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('maiden_name', 255)->nullable();
            $table->string('birthplace', 255);
            $table->string('phone', 255);
            $table->string('address_country', 255);
            $table->string('address_zipcode', 255);
            $table->string('address_county', 255);
            $table->string('address_location', 255);
            $table->string('address_street', 255);
            $table->string('address_number', 255);
            $table->string('mothers_name', 255)->nullable();
            $table->string('mothers_phone', 255)->nullable();
            $table->string('mothers_email', 255)->nullable();
            $table->string('fathers_name', 255)->nullable();
            $table->string('fathers_phone', 255)->nullable();
            $table->string('fathers_email', 255)->nullable();
            $table->string('legal_representative_name', 255)->nullable();
            $table->string('legal_representative_phone', 255)->nullable();
            $table->string('legal_representative_email', 255)->nullable();
            $table->string('nationality', 255)->nullable();
            $table->string('elementary_school', 255)->nullable();
            $table->string('primary_school', 255)->nullable();
            $table->string('secondary_school', 255)->nullable();
            $table->string('post_secondary_school', 255)->nullable();
            $table->string('college', 255)->nullable();
            $table->string('university', 255)->nullable();
            $table->string('other_trainings', 255)->nullable();
            $table->string('foreign_language_knowledge', 255)->nullable();
            $table->string('occupation', 255)->nullable();
            $table->string('workplace', 255)->nullable();
            $table->text('comment')->nullable();
            $table->date('nameday')->nullable();            
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_scouts');
    }
}
