<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarScouts10 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
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
            $table->boolean('is_active')->default(1)->change();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropColumn('name_prefix');
            $table->dropColumn('nickname');
            $table->dropColumn('birthdate');
            $table->dropColumn('maiden_name');
            $table->dropColumn('birthplace');
            $table->dropColumn('phone');
            $table->dropColumn('address_country');
            $table->dropColumn('address_zipcode');
            $table->dropColumn('address_county');
            $table->dropColumn('address_location');
            $table->dropColumn('address_street');
            $table->dropColumn('address_number');
            $table->dropColumn('mothers_name');
            $table->dropColumn('mothers_phone');
            $table->dropColumn('mothers_email');
            $table->dropColumn('fathers_name');
            $table->dropColumn('fathers_phone');
            $table->dropColumn('fathers_email');
            $table->dropColumn('legal_representative_name');
            $table->dropColumn('legal_representative_phone');
            $table->dropColumn('legal_representative_email');
            $table->dropColumn('nationality');
            $table->dropColumn('elementary_school');
            $table->dropColumn('primary_school');
            $table->dropColumn('secondary_school');
            $table->dropColumn('post_secondary_school');
            $table->dropColumn('college');
            $table->dropColumn('university');
            $table->dropColumn('other_trainings');
            $table->dropColumn('foreign_language_knowledge');
            $table->dropColumn('occupation');
            $table->dropColumn('workplace');
            $table->dropColumn('comment');
            $table->dropColumn('nameday');
            $table->boolean('is_active')->default(null)->change();
        });
    }
}
