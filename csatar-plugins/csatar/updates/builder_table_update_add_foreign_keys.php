<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAddForeignKeys extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_age_group_team_report', function($table)
        {
            $table->primary(['age_group_id','team_report_id'], 'csatar_csatar_age_group_id_team_report_id_primary');
            $table->foreign('age_group_id', 'age_group_team_report_age_group_id_foreign')->references('id')->on('csatar_csatar_age_groups');
            $table->foreign('team_report_id', 'age_group_team_report_team_report_id_foreign')->references('id')->on('csatar_csatar_team_reports');
        });
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->foreign('currency_id')->references('id')->on('csatar_csatar_currencies');
        });
        Schema::table('csatar_csatar_associations_legal_relationships', function($table)
        {
            $table->foreign('association_id', 'associations_legal_relationships_association_id_foreign')->references('id')->on('csatar_csatar_associations');
            $table->foreign('legal_relationship_id', 'associations_legal_relationships_legal_relationship_id_foreign')->references('id')->on('csatar_csatar_legal_relationships');
        });
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->foreign('association_id')->references('id')->on('csatar_csatar_associations');
        });
        Schema::table('csatar_csatar_hierarchy', function($table)
        {
            $table->index('parent_id');
        });
        Schema::table('csatar_csatar_mandates', function($table)
        {
            $table->foreign('scout_id')->references('id')->on('csatar_csatar_scouts');
            $table->index('scout_id');
            $table->foreign('mandate_type_id')->references('id')->on('csatar_csatar_mandate_types');
            $table->index('mandate_type_id');
            $table->index('mandate_model_id');
        });
        Schema::table('csatar_csatar_mandate_types', function($table)
        {
            $table->foreign('association_id')->references('id')->on('csatar_csatar_associations');
            $table->index('parent_id');
        });
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->foreign('team_id')->references('id')->on('csatar_csatar_teams');
            $table->foreign('troop_id')->references('id')->on('csatar_csatar_troops');
        });
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->foreign('team_id')->references('id')->on('csatar_csatar_teams');
            $table->foreign('troop_id')->references('id')->on('csatar_csatar_troops');
            $table->foreign('patrol_id')->references('id')->on('csatar_csatar_patrols');
            $table->foreign('legal_relationship_id')->references('id')->on('csatar_csatar_legal_relationships');
            $table->foreign('special_diet_id')->references('id')->on('csatar_csatar_special_diets');
            $table->foreign('religion_id')->references('id')->on('csatar_csatar_religions');
            $table->foreign('tshirt_size_id')->references('id')->on('csatar_csatar_tshirt_sizes');
        });
        Schema::table('csatar_csatar_scouts_allergies', function($table)
        {
            $table->foreign('scout_id', 'scouts_allergies_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('allergy_id', 'scouts_allergies_allergy_id_foreign')->references('id')->on('csatar_csatar_allergies');
        });
        Schema::table('csatar_csatar_scouts_chronic_illnesses', function($table)
        {
            $table->foreign('scout_id', 'scouts_chronic_illnesses_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('chronic_illness_id', 'scouts_chronic_illnesses_chronic_illness_id_foreign')->references('id')->on('csatar_csatar_chronic_illnesses');
        });
        Schema::table('csatar_csatar_scouts_food_sensitivities', function($table)
        {
            $table->foreign('scout_id', 'scouts_food_sensitivities_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('food_sensitivity_id', 'scouts_food_sensitivities_food_sensitivity_id_foreign')->references('id')->on('csatar_csatar_food_sensitivities');
        });
        Schema::table('csatar_csatar_scouts_leadership_qualifications', function($table)
        {
            $table->foreign('scout_id', 'scouts_leadership_qualifications_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('leadership_qualification_id', 'scouts_leadership_qualifications_lead_qualification_id_foreign')->references('id')->on('csatar_csatar_leadership_qualifications');
            $table->foreign('training_id', 'scouts_leadership_qualifications_training_id_foreign')->references('id')->on('csatar_csatar_trainings');
            $table->index('training_id');
        });
        Schema::table('csatar_csatar_scouts_professional_qualifications', function($table)
        {
            $table->foreign('scout_id', 'scouts_professional_qualifications_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('professional_qualification_id', 'scouts_professional_qualifications_prof_qualification_id_foreign')->references('id')->on('csatar_csatar_professional_qualifications');
        });
        Schema::table('csatar_csatar_scouts_promises', function($table)
        {
            $table->foreign('scout_id', 'scouts_promises_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('promise_id', 'scouts_promises_promise_id_foreign')->references('id')->on('csatar_csatar_promises');
        });
        Schema::table('csatar_csatar_scouts_special_qualifications', function($table)
        {
            $table->foreign('scout_id', 'scouts_special_qualifications_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('special_qualification_id', 'scouts_special_qualifications_special_qualification_id_foreign')->references('id')->on('csatar_csatar_special_qualifications');
        });
        Schema::table('csatar_csatar_scouts_special_tests', function($table)
        {
            $table->foreign('scout_id', 'scouts_special_tests_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('special_test_id', 'scouts_special_tests_special_test_id_foreign')->references('id')->on('csatar_csatar_special_tests');
        });
        Schema::table('csatar_csatar_scouts_tests', function($table)
        {
            $table->foreign('scout_id', 'scouts_tests_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('test_id', 'scouts_tests_test_id_foreign')->references('id')->on('csatar_csatar_tests');
        });
        Schema::table('csatar_csatar_scouts_training_qualifications', function($table)
        {
            $table->foreign('scout_id', 'scouts_training_qualifications_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('training_qualification_id', 'scouts_training_qualifications_training_qualification_id_foreign')->references('id')->on('csatar_csatar_training_qualifications');
            $table->foreign('training_id', 'scouts_training_qualifications_training_id_foreign')->references('id')->on('csatar_csatar_trainings');
            $table->index('training_id');
        });
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->foreign('district_id')->references('id')->on('csatar_csatar_districts');
        });
        Schema::table('csatar_csatar_team_reports', function($table)
        {
            $table->foreign('team_id')->references('id')->on('csatar_csatar_teams');
            $table->foreign('spiritual_leader_religion_id')->references('id')->on('csatar_csatar_religions');
            $table->foreign('currency_id')->references('id')->on('csatar_csatar_currencies');
        });
        Schema::table('csatar_csatar_team_reports_scouts', function($table)
        {
            $table->foreign('team_report_id', 'team_reports_scouts_team_report_id_foreign')->references('id')->on('csatar_csatar_team_reports');
            $table->foreign('scout_id', 'team_reports_scouts_scout_id_foreign')->references('id')->on('csatar_csatar_scouts');
            $table->foreign('legal_relationship_id', 'team_reports_scouts_legal_relationship_id_foreign')->references('id')->on('csatar_csatar_legal_relationships');
            $table->foreign('leadership_qualification_id', 'team_reports_scouts_leadership_qualification_id_foreign')->references('id')->on('csatar_csatar_leadership_qualifications');
        });
        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->foreign('team_id')->references('id')->on('csatar_csatar_teams');
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_age_group_team_report', function($table)
        {
            $table->dropForeign('age_group_team_report_age_group_id_foreign');
            $table->dropForeign('age_group_team_report_team_report_id_foreign');
            $table->dropPrimary('csatar_csatar_age_group_id_team_report_id_primary');
        });
        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropForeign('csatar_csatar_associations_currency_id_foreign');
        });
        Schema::table('csatar_csatar_associations_legal_relationships', function($table)
        {
            $table->dropForeign('associations_legal_relationships_association_id_foreign');
            $table->dropForeign('associations_legal_relationships_legal_relationship_id_foreign');
        });
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->dropForeign('csatar_csatar_districts_association_id_foreign');
        });
        Schema::table('csatar_csatar_hierarchy', function($table)
        {
            $table->dropIndex('csatar_csatar_hierarchy_parent_id_index');
        });
        Schema::table('csatar_csatar_mandates', function($table)
        {
            $table->dropForeign('csatar_csatar_mandates_scout_id_foreign');
            $table->dropIndex('csatar_csatar_mandates_scout_id_index');
            $table->dropForeign('csatar_csatar_mandates_mandate_type_id_foreign');
            $table->dropIndex('csatar_csatar_mandates_mandate_type_id_index');
            $table->dropIndex('csatar_csatar_mandates_mandate_model_id_index');
        });
        Schema::table('csatar_csatar_mandate_types', function($table)
        {
            $table->dropForeign('csatar_csatar_mandate_types_association_id_foreign');
            $table->dropIndex('csatar_csatar_mandate_types_parent_id_index');
        });
        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->dropForeign('csatar_csatar_patrols_team_id_foreign');
            $table->dropForeign('csatar_csatar_patrols_troop_id_foreign');
        });
        Schema::table('csatar_csatar_scouts', function($table)
        {
            $table->dropForeign('csatar_csatar_scouts_user_id_foreign');
            $table->dropIndex('csatar_csatar_scouts_user_id_index');
            $table->dropForeign('csatar_csatar_scouts_team_id_foreign');
            $table->dropForeign('csatar_csatar_scouts_troop_id_foreign');
            $table->dropForeign('csatar_csatar_scouts_patrol_id_foreign');
            $table->dropForeign('csatar_csatar_scouts_legal_relationship_id_foreign');
            $table->dropForeign('csatar_csatar_scouts_special_diet_id_foreign');
            $table->dropForeign('csatar_csatar_scouts_religion_id_foreign');
            $table->dropForeign('csatar_csatar_scouts_tshirt_size_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_allergies', function($table)
        {
            $table->dropForeign('scouts_allergies_scout_id_foreign');
            $table->dropForeign('scouts_allergies_allergy_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_chronic_illnesses', function($table)
        {
            $table->dropForeign('scouts_chronic_illnesses_scout_id_foreign');
            $table->dropForeign('scouts_chronic_illnesses_chronic_illness_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_food_sensitivities', function($table)
        {
            $table->dropForeign('scouts_food_sensitivities_scout_id_foreign');
            $table->dropForeign('scouts_food_sensitivities_food_sensitivity_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_leadership_qualifications', function($table)
        {
            $table->dropForeign('scouts_leadership_qualifications_scout_id_foreign');
            $table->dropForeign('scouts_leadership_qualifications_lead_qualification_id_foreign');
            $table->dropForeign('scouts_leadership_qualifications_training_id_foreign');
            $table->dropIndex('csatar_csatar_scouts_leadership_qualifications_training_id_index');
        });
        Schema::table('csatar_csatar_scouts_professional_qualifications', function($table)
        {
            $table->dropForeign('scouts_professional_qualifications_scout_id_foreign');
            $table->dropForeign('scouts_professional_qualifications_prof_qualification_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_promises', function($table)
        {
            $table->dropForeign('scouts_promises_scout_id_foreign');
            $table->dropForeign('scouts_promises_promise_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_special_qualifications', function($table)
        {
            $table->dropForeign('scouts_special_qualifications_scout_id_foreign');
            $table->dropForeign('scouts_special_qualifications_special_qualification_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_special_tests', function($table)
        {
            $table->dropForeign('scouts_special_tests_scout_id_foreign');
            $table->dropForeign('scouts_special_tests_special_test_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_tests', function($table)
        {
            $table->dropForeign('scouts_tests_scout_id_foreign');
            $table->dropForeign('scouts_tests_test_id_foreign');
        });
        Schema::table('csatar_csatar_scouts_training_qualifications', function($table)
        {
            $table->dropForeign('scouts_training_qualifications_scout_id_foreign');
            $table->dropForeign('scouts_training_qualifications_training_qualification_id_foreign');
            $table->dropForeign('scouts_training_qualifications_training_id_foreign');
            $table->dropIndex('csatar_csatar_scouts_training_qualifications_training_id_index');
        });
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->dropForeign('csatar_csatar_teams_district_id_foreign');
        });
        Schema::table('csatar_csatar_team_reports', function($table)
        {
            $table->dropForeign('csatar_csatar_team_reports_team_id_foreign');
            $table->dropForeign('csatar_csatar_team_reports_spiritual_leader_religion_id_foreign');
            $table->dropForeign('csatar_csatar_team_reports_currency_id_foreign');
        });
        Schema::table('csatar_csatar_team_reports_scouts', function($table)
        {
            $table->dropForeign('team_reports_scouts_team_report_id_foreign');
            $table->dropForeign('team_reports_scouts_scout_id_foreign');
            $table->dropForeign('team_reports_scouts_legal_relationship_id_foreign');
            $table->dropForeign('team_reports_scouts_leadership_qualification_id_foreign');
        });
        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->dropForeign('csatar_csatar_troops_team_id_foreign');
        });
    }

}
