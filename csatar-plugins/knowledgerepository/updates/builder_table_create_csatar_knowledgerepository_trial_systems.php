<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryTrialSystems extends Migration
{

    public function up()
    {
        Schema::create('csatar_knowledgerepository_trial_systems', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('association_id')->unsigned();
            $table->string('id_string', 15)->nullable();
            $table->string('name');
            $table->integer('trial_system_category_id')->nullable()->unsigned();
            $table->integer('trial_system_topic_id')->nullable()->unsigned();
            $table->integer('trial_system_sub_topic_id')->nullable()->unsigned();
            $table->integer('age_group_id')->nullable()->unsigned();
            $table->integer('trial_system_type_id')->nullable()->unsigned();
            $table->integer('trial_system_trial_type_id')->nullable()->unsigned();
            $table->boolean('for_patrols')->nullable();
            $table->boolean('individual')->nullable();
            $table->boolean('task')->nullable();
            $table->boolean('obligatory')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('association_id', 'association_foreign')->references('id')->on('csatar_csatar_associations');
            $table->foreign('trial_system_category_id', 'trial_system_category_foreign')->references('id')->on('csatar_knowledgerepository_trial_system_categories');
            $table->foreign('trial_system_topic_id', 'trial_system_topic_foreign')->references('id')->on('csatar_knowledgerepository_trial_system_topics');
            $table->foreign('trial_system_sub_topic_id', 'trial_system_sub_topic_foreign')->references('id')->on('csatar_knowledgerepository_trial_system_subtopics');
            $table->foreign('age_group_id', 'age_group_foreign')->references('id')->on('csatar_csatar_age_groups');
            $table->foreign('trial_system_type_id', 'trial_system_type_foreign')->references('id')->on('csatar_knowledgerepository_trial_system_types');
            $table->foreign('trial_system_trial_type_id', 'trial_system_trial_type_foreign')->references('id')->on('csatar_knowledgerepository_trial_system_trial_types');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_trial_systems');
    }

}
