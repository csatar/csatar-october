<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1064 extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->string('contact_name', 255)->nullable()->change();
            $table->string('contact_email', 255)->nullable()->change();
            $table->string('address', 255)->nullable()->change();
            $table->text('leadership_presentation')->nullable()->change();
        });

        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->string('address', 255)->nullable()->change();
            $table->string('phone', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->string('contact_name', 255)->nullable()->change();
            $table->string('contact_email', 255)->nullable()->change();
            $table->text('leadership_presentation')->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->string('address', 255)->nullable()->change();
            $table->date('foundation_date')->nullable()->change();
            $table->string('phone', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
            $table->string('contact_name', 255)->nullable()->change();
            $table->string('contact_email', 255)->nullable()->change();
            $table->text('leadership_presentation')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->string('juridical_person_name', 255)->nullable()->change();
            $table->string('juridical_person_address', 255)->nullable()->change();
            $table->string('juridical_person_tax_number', 255)->nullable()->change();
            $table->string('juridical_person_bank_account', 255)->nullable()->change();
        });

        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->string('family_name', 255)->nullable()->change();
            $table->string('given_name', 255)->nullable()->change();
            $table->smallInteger('gender')->nullable()->change();
            $table->string('personal_identification_number', 20)->nullable()->change();
            $table->string('birthplace', 255)->nullable()->change();
            $table->string('phone', 255)->nullable()->change();
            $table->string('address_country', 255)->nullable()->change();
            $table->string('address_zipcode', 255)->nullable()->change();
            $table->string('address_county', 255)->nullable()->change();
            $table->string('address_location', 255)->nullable()->change();
            $table->string('address_street', 255)->nullable()->change();
            $table->string('address_number', 255)->nullable()->change();
        });

        Schema::table('csatar_csatar_team_reports', function($table)
        {
            $table->integer('number_of_adult_patrols')->nullable()->change();
            $table->integer('number_of_explorer_patrols')->nullable()->change();
            $table->integer('number_of_scout_patrols')->nullable()->change();
            $table->integer('number_of_cub_scout_patrols')->nullable()->change();
            $table->integer('number_of_mixed_patrols')->nullable()->change();
            $table->dropForeign('csatar_csatar_team_reports_spiritual_leader_religion_id_foreign');
            $table->integer('spiritual_leader_religion_id')->nullable()->unsigned()->change();
            $table->foreign('spiritual_leader_religion_id')->references('id')->on('csatar_csatar_religions');
            $table->text('scouting_year_report_team_camp')->nullable()->change();
            $table->text('scouting_year_report_homesteading')->nullable()->change();
            $table->text('scouting_year_report_programs')->nullable()->change();
            $table->text('scouting_year_team_applications')->nullable()->change();
            $table->string('spiritual_leader_name', 255)->nullable()->change();
            $table->string('spiritual_leader_occupation', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->string('contact_name', 255)->nullable(false)->change();
            $table->string('contact_email', 255)->nullable(false)->change();
            $table->string('address', 255)->nullable(false)->change();
            $table->text('leadership_presentation')->nullable(false)->change();
        });

        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->string('address', 255)->nullable(false)->change();
            $table->string('phone', 255)->nullable(false)->change();
            $table->string('email', 255)->nullable(false)->change();
            $table->string('contact_name', 255)->nullable(false)->change();
            $table->string('contact_email', 255)->nullable(false)->change();
            $table->text('leadership_presentation')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });

        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->string('address', 255)->nullable(false)->change();
            $table->date('foundation_date')->nullable(false)->change();
            $table->string('phone', 255)->nullable(false)->change();
            $table->string('email', 255)->nullable(false)->change();
            $table->string('contact_name', 255)->nullable(false)->change();
            $table->string('contact_email', 255)->nullable(false)->change();
            $table->text('leadership_presentation')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->string('juridical_person_name', 255)->nullable(false)->change();
            $table->string('juridical_person_address', 255)->nullable(false)->change();
            $table->string('juridical_person_tax_number', 255)->nullable(false)->change();
            $table->string('juridical_person_bank_account', 255)->nullable(false)->change();
        });

        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->string('family_name', 255)->nullable(false)->change();
            $table->string('given_name', 255)->nullable(false)->change();
            $table->smallInteger('gender')->nullable(false)->change();
            $table->string('personal_identification_number', 20)->nullable(false)->change();
            $table->string('birthplace', 255)->nullable(false)->change();
            $table->string('phone', 255)->nullable(false)->change();
            $table->string('address_country', 255)->nullable(false)->change();
            $table->string('address_zipcode', 255)->nullable(false)->change();
            $table->string('address_county', 255)->nullable(false)->change();
            $table->string('address_location', 255)->nullable(false)->change();
            $table->string('address_street', 255)->nullable(false)->change();
            $table->string('address_number', 255)->nullable(false)->change();
        });

        Schema::table('csatar_csatar_team_reports', function($table)
        {
            $table->integer('number_of_adult_patrols')->nullable(false)->change();
            $table->integer('number_of_explorer_patrols')->nullable(false)->change();
            $table->integer('number_of_scout_patrols')->nullable(false)->change();
            $table->integer('number_of_cub_scout_patrols')->nullable(false)->change();
            $table->integer('number_of_mixed_patrols')->nullable(false)->change();
            $table->dropForeign('csatar_csatar_team_reports_spiritual_leader_religion_id_foreign');
            $table->integer('spiritual_leader_religion_id')->nullable(false)->unsigned()->change();
            $table->foreign('spiritual_leader_religion_id')->references('id')->on('csatar_csatar_religions');
            $table->text('scouting_year_report_team_camp')->nullable(false)->change();
            $table->text('scouting_year_report_homesteading')->nullable(false)->change();
            $table->text('scouting_year_report_programs')->nullable(false)->change();
            $table->text('scouting_year_team_applications')->nullable(false)->change();
            $table->string('spiritual_leader_name', 255)->nullable(false)->change();
            $table->string('spiritual_leader_occupation', 255)->nullable(false)->change();
        });

    }

}
